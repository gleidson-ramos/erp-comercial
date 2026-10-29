<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $digits) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('fantasy_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");

                if ($digits !== '') {
                    $q->orWhere('document', 'like', "%{$digits}%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        if ($request->filled('person_type')) {
            $query->where(
                'person_type',
                strtoupper($request->input('person_type'))
            );
        }

        $suppliers = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSupplier($request);

        Supplier::create($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Fornecedor cadastrado com sucesso.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateSupplier($request, $supplier);

        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso.');
    }

    public function deactivate(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Fornecedor desativado com sucesso.');
    }

    private function validateSupplier(
        Request $request,
        ?Supplier $supplier = null
    ): array {
        $request->merge([
            'person_type' => strtoupper(trim(
                (string) $request->input('person_type')
            )),
            'name' => trim((string) $request->input('name')),
            'fantasy_name' => $request->filled('fantasy_name')
                ? trim($request->input('fantasy_name'))
                : null,
            'document' => $request->filled('document')
                ? preg_replace('/\D/', '', $request->input('document'))
                : null,
            'postal_code' => $request->filled('postal_code')
                ? preg_replace('/\D/', '', $request->input('postal_code'))
                : null,
            'state' => $request->filled('state')
                ? strtoupper(trim($request->input('state')))
                : null,
            'is_active' => $supplier !== null
                ? $request->boolean('is_active')
                : true,
        ]);

        $data = $request->validate([
            'person_type' => [
                'required',
                Rule::in(['PF', 'PJ']),
            ],
            'name' => ['required', 'string', 'max:150'],
            'fantasy_name' => ['nullable', 'string', 'max:150'],
            'document' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('suppliers', 'document')->ignore($supplier),
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value) {
                        return;
                    }

                    $type = $request->input('person_type');

                    if ($type === 'PF' && strlen($value) !== 11) {
                        $fail('O CPF deve conter 11 dígitos.');
                        return;
                    }

                    if ($type === 'PJ' && strlen($value) !== 14) {
                        $fail('O CNPJ deve conter 14 dígitos.');
                        return;
                    }

                    if (!$this->isValidCpfCnpj($value)) {
                        $fail('O CPF ou CNPJ informado é inválido.');
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'postal_code' => [
                'nullable',
                'regex:/^\d{8}$/',
            ],
            'street' => ['nullable', 'string', 'max:150'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => [
                'nullable',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ], [
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'person_type.in' => 'O tipo de pessoa deve ser PF ou PJ.',
            'name.required' => 'Informe o nome ou a razão social.',
            'name.max' => 'O nome deve ter no máximo 150 caracteres.',
            'fantasy_name.max' => 'O nome fantasia deve ter no máximo 150 caracteres.',
            'document.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'email.email' => 'Informe um e-mail válido.',
            'postal_code.regex' => 'O CEP deve conter exatamente 8 dígitos.',
            'state.size' => 'A UF deve conter 2 caracteres.',
            'state.regex' => 'Informe uma UF válida com duas letras.',
        ]);

        return $data;
    }

    private function isValidCpfCnpj(string $document): bool
    {
        if (preg_match('/^(\d)\1+$/', $document)) {
            return false;
        }

        if (strlen($document) === 11) {
            $sum = 0;

            for ($i = 0; $i < 9; $i++) {
                $sum += (int) $document[$i] * (10 - $i);
            }

            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;

            if ($digit !== (int) $document[9]) {
                return false;
            }

            $sum = 0;

            for ($i = 0; $i < 10; $i++) {
                $sum += (int) $document[$i] * (11 - $i);
            }

            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;

            return $digit === (int) $document[10];
        }

        if (strlen($document) === 14) {
            $weightSets = [
                [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
                [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            ];

            foreach ($weightSets as $index => $weights) {
                $position = 12 + $index;
                $sum = 0;

                foreach ($weights as $i => $weight) {
                    $sum += (int) $document[$i] * $weight;
                }

                $remainder = $sum % 11;
                $digit = $remainder < 2 ? 0 : 11 - $remainder;

                if ($digit !== (int) $document[$position]) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}