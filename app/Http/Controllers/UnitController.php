<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $query = Unit::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere(
                        'abbreviation',
                        'ilike',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        $units = $query
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUnit($request);

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unidade cadastrada com sucesso.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(
        Request $request,
        Unit $unit
    ): RedirectResponse {
        $validated = $this->validateUnit($request, $unit);

        $unit->update($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unidade atualizada com sucesso.');
    }

    public function deactivate(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()
                ->route('units.index')
                ->with(
                    'error',
                    'Esta unidade está vinculada a produtos. '
                    . 'Transfira esses produtos para outra unidade '
                    . 'antes de desativá-la.'
                );
        }

        $unit->update(['is_active' => false]);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unidade desativada com sucesso.');
    }

    /**
     * Centraliza as regras de validação da unidade de medida.
     */
    private function validateUnit(
        Request $request,
        ?Unit $unit = null
    ): array {
        // Normaliza a abreviação antes da validação.
        $request->merge([
            'abbreviation' => strtoupper(
                trim((string) $request->input('abbreviation'))
            ),
            'name' => trim((string) $request->input('name')),
        ]);

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:60',
                Rule::unique('units', 'name')->ignore($unit),
            ],
            'abbreviation' => [
                'required',
                'string',
                'max:10',
                Rule::unique('units', 'abbreviation')->ignore($unit),
            ],
        ], [
            'name.required' => 'O nome da unidade é obrigatório.',
            'name.string' => 'O nome da unidade deve ser um texto válido.',
            'name.max' => 'O nome da unidade não pode ultrapassar 60 caracteres.',
            'name.unique' => 'Já existe uma unidade cadastrada com este nome.',
            'abbreviation.required' => 'A abreviação da unidade é obrigatória.',
            'abbreviation.string' => 'A abreviação deve ser um texto válido.',
            'abbreviation.max' => 'A abreviação não pode ultrapassar 10 caracteres.',
            'abbreviation.unique' => 'Já existe uma unidade cadastrada com esta abreviação.',
        ]);
    }
}