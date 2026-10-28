<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'unit', 'inventoryStock']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($request->input('status') === 'low') {
            $query->whereRaw(
                'COALESCE(
                    (SELECT inventory_stocks.quantity
                     FROM inventory_stocks
                     WHERE inventory_stocks.product_id = products.id),
                    0
                ) <= products.minimum_stock'
            );
        } elseif ($request->input('status') === 'available') {
            $query->whereRaw(
                'COALESCE(
                    (SELECT inventory_stocks.quantity
                     FROM inventory_stocks
                     WHERE inventory_stocks.product_id = products.id),
                    0
                ) > products.minimum_stock'
            );
        }

        $products = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('stock.index', compact('products'));
    }

    public function create(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        return view('stock.create', compact('products'));
    }

    public function store(
        Request $request,
        StockMovementService $stockMovementService
    ): RedirectResponse {
        $validated = $this->validateMovement($request);

        $stockMovementService->register($validated);

        return redirect()
            ->route('stock.history')
            ->with('success', 'Movimentação registrada com sucesso.');
    }

    public function history(Request $request): View
    {
        $query = StockMovement::query()
            ->with(['product.unit']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->whereHas('product', function ($builder) use ($search) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('moved_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('moved_at', '<=', $request->input('date_to'));
        }

        $movements = $query
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('stock.history', compact('movements'));
    }

    /**
     * Centraliza as regras de validação das movimentações.
     */
    private function validateMovement(Request $request): array
    {
        $request->merge([
            'reason' => trim((string) $request->input('reason')),
        ]);

        return $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],
            'type' => [
                'required',
                Rule::in([
                    StockMovement::TYPE_IN,
                    StockMovement::TYPE_OUT,
                ]),
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:99999999999.999',
            ],
            'unit_cost' => [
                'nullable',
                'required_if:type,' . StockMovement::TYPE_IN,
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
            'reason' => [
                'required',
                Rule::in([
                    'COMPRA',
                    'DEVOLUCAO',
                    'AJUSTE',
                    'PERDA',
                    'USO_INTERNO',
                    'OUTRO',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'product_id.required' => 'Selecione um produto.',
            'product_id.exists' => 'O produto selecionado não existe ou está inativo.',
            'type.required' => 'Selecione o tipo de movimentação.',
            'type.in' => 'O tipo de movimentação é inválido.',
            'quantity.required' => 'Informe a quantidade.',
            'quantity.numeric' => 'A quantidade deve ser numérica.',
            'quantity.gt' => 'A quantidade deve ser maior que zero.',
            'quantity.decimal' => 'A quantidade pode ter até três casas decimais.',
            'quantity.max' => 'A quantidade informada é muito alta.',
            'unit_cost.required_if' => 'Informe o custo unitário para entradas.',
            'unit_cost.numeric' => 'O custo unitário deve ser numérico.',
            'unit_cost.min' => 'O custo unitário não pode ser negativo.',
            'unit_cost.decimal' => 'O custo unitário pode ter até duas casas decimais.',
            'reason.required' => 'Selecione o motivo da movimentação.',
            'reason.in' => 'Selecione um motivo válido.',
            'notes.max' => 'As observações não podem ultrapassar 2.000 caracteres.',
        ]);
    }
}