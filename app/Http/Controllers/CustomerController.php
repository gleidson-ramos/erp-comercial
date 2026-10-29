<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $digits = preg_replace('/\D/', '', $search);

            $query->where(function ($builder) use ($search, $digits) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");

                if ($digits !== '') {
                    $builder->orWhere('document', 'like', "%{$digits}%");
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
            $query->where('person_type', $request->input('person_type'));
        }

        $customers = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(
        Request $request,
        Customer $customer
    ): RedirectResponse {
        $validated = $this->validateCustomer($request, $customer);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function deactivate(Customer $customer): RedirectResponse
    {
        $customer->update(['is_active' => false]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente desativado com sucesso.');
    }

    /**
     * Centraliza as regras de validação do cliente.
     */
    private function validateCustomer(
        Request $request,
        ?Customer $customer = null
    ): array {
        $document = preg_replace(
            '/\D/',
            '',
            (string) $request->input('document', '')
        );

        $postalCode = preg_replace(
            '/\D/',
            '',
            (string) $request->input('postal_code', '')
        );

        $request->merge([
            'person_type' => strtoupper(
                trim((string) $request->input('person_type'))
            ),
            'name' => trim((string) $request->input('name')),
            'document' => $document !== '' ? $document : null,
            'postal_code' => $postalCode !== '' ? $postalCode : null,
            'state' => strtoupper(
                trim((string) $request->input('state'))
            ),
        ]);

        if ($customer !== null) {
            $request->merge([
                'is_active' => $request->boolean('is_active'),
            ]);
        } else {
            $request->merge([
                'is_active' => true,
            ]);
        }

        return $request->validate([
            'is_active' => ['required', 'boolean'],
            
            'person_type' => [
                'required',
                Rule::in(['PF', 'PJ']),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'document' => [
                'nullable',
                'string',
                Rule::unique('customers', 'document')->ignore($customer),
                function ($attribute, $value, $fail) use ($request) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $type = $request->input('person_type');
                    $expectedLength = $type === 'PF' ? 11 : 14;

                    if (strlen($value) !== $expectedLength) {
                        $fail(
                            $type === 'PF'
                                ? 'O CPF deve conter 11 dígitos.'
                                : 'O CNPJ deve conter 14 dígitos.'
                        );

                        return;
                    }

                    if (!$this->isValidCpfCnpj($value, $type)) {
                        $fail(
                            $type === 'PF'
                                ? 'O CPF informado é inválido.'
                                : 'O CNPJ informado é inválido.'
                        );
                    }
                },
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'postal_code' => [
                'nullable',
                'digits:8',
            ],

            'street' => [
                'nullable',
                'string',
                'max:150',
            ],

            'number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'complement' => [
                'nullable',
                'string',
                'max:100',
            ],

            'neighborhood' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'person_type.required' => 'Selecione o tipo de pessoa.',
            'person_type.in' => 'O tipo de pessoa deve ser física ou jurídica.',
            'name.required' => 'O nome do cliente é obrigatório.',
            'name.max' => 'O nome não pode ultrapassar 150 caracteres.',
            'document.unique' => 'Já existe um cliente cadastrado com este CPF/CNPJ.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O e-mail não pode ultrapassar 150 caracteres.',
            'postal_code.digits' => 'O CEP deve conter exatamente 8 dígitos.',
            'state.size' => 'A UF deve conter exatamente 2 letras.',
            'state.regex' => 'Informe a UF usando duas letras.',
            'notes.max' => 'As observações não podem ultrapassar 2.000 caracteres.',
        ]);
    }

    //Verifica CPF ou CNPJ.
    private function isValidCpfCnpj(string $document, string $type): bool
    {
        if ($type === 'PF') {
            if (strlen($document) !== 11
                || preg_match('/^(\d)\1{10}$/', $document)) {
                return false;
            }

            for ($position = 9; $position <= 10; $position++) {
                $sum = 0;

                for ($i = 0; $i < $position; $i++) {
                    $sum += (int) $document[$i]
                        * (($position + 1) - $i);
                }

                $remainder = ($sum * 10) % 11;
                $digit = $remainder === 10 ? 0 : $remainder;

                if ($digit !== (int) $document[$position]) {
                    return false;
                }
            }

            return true;
        }

        if ($type !== 'PJ'
            || strlen($document) !== 14
            || preg_match('/^(\d)\1{13}$/', $document)) {
            return false;
        }

        
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
}