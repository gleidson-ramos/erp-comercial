<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        $categories = $query
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria cadastrada com sucesso.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(
        Request $request,
        Category $category
    ): RedirectResponse {
        $validated = $this->validateCategory($request, $category);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function deactivate(Category $category): RedirectResponse
    {
        $category->update(['is_active' => false]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria desativada com sucesso.');
    }

    /**
     * Centraliza as regras de validação da categoria.
     */
    private function validateCategory(
        Request $request,
        ?Category $category = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($category),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ], [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.string' => 'O nome da categoria deve ser um texto válido.',
            'name.max' => 'O nome da categoria não pode ultrapassar 100 caracteres.',
            'name.unique' => 'Já existe uma categoria cadastrada com este nome.',
            'description.string' => 'A descrição deve ser um texto válido.',
        ]);
    }
}