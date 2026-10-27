<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'unit']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        $products = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'products.edit',
            compact('product', 'categories', 'units')
        );
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $this->validateProduct($request, $product);

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function deactivate(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto desativado com sucesso.');
    }

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        return $request->validate([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true),
            ],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')
                    ->where('is_active', true),
            ],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($product),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'gt:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
        ], [
            'sku.unique' => 'Este SKU já está cadastrado.',
            'barcode.unique' => 'Este código de barras já está cadastrado.',
            'name.required' => 'Informe o nome do produto.',
            'unit_id.required' => 'Selecione uma unidade de medida.',
            'sale_price.gt' => 'O preço de venda deve ser maior que zero.',
        ]);
    }
}