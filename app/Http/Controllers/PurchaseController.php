<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchaseService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Purchase::query()
            ->with('supplier')
            ->withCount('items');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($builder) use ($search) {
                $builder->where('number', 'ILIKE', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('fantasy_name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $purchases = $query
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statuses = [
            Purchase::STATUS_DRAFT => 'Rascunho',
            Purchase::STATUS_CONFIRMED => 'Confirmada',
            Purchase::STATUS_PARTIAL => 'Parcialmente recebida',
            Purchase::STATUS_RECEIVED => 'Recebida',
            Purchase::STATUS_CANCELLED => 'Cancelada',
        ];

        return view('purchases.index', compact(
            'purchases',
            'statuses'
        ));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact(
            'suppliers',
            'products'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePurchase($request);

        $purchase = $this->purchaseService->create($data);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Compra cadastrada como rascunho.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load([
            'supplier',
            'items.product',
            'receipts.items.purchaseItem.product',
        ]);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View|RedirectResponse
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'Somente compras em rascunho podem ser editadas.');
        }

        $purchase->load('items.product');

        $suppliers = Supplier::query()
            ->where(function ($query) use ($purchase) {
                $query->where('is_active', true)
                    ->orWhere('id', $purchase->supplier_id);
            })
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.edit', compact(
            'purchase',
            'suppliers',
            'products'
        ));
    }

    public function update(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        $data = $this->validatePurchase($request, $purchase);

        $this->purchaseService->update($purchase, $data);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Compra atualizada com sucesso.');
    }

    public function confirm(Purchase $purchase): RedirectResponse
    {
        $this->purchaseService->confirm($purchase);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Compra confirmada com sucesso.');
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        if (! in_array($purchase->status, [
            Purchase::STATUS_DRAFT,
            Purchase::STATUS_CONFIRMED,
        ], true)) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'Esta compra não pode ser cancelada no estado atual.');
        }

        if ($purchase->receipts()->exists()) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'Não é possível cancelar uma compra que já possui recebimentos.');
        }

        $purchase->update([
            'status' => Purchase::STATUS_CANCELLED,
        ]);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Compra cancelada.');
    }

    private function validatePurchase(
        Request $request,
        ?Purchase $purchase = null
    ): array {
        return $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')
                    ->where('is_active', true),
            ],

            'number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('purchases', 'number')
                    ->ignore($purchase?->id),
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:purchase_date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:99999999999.999',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
        ], [
            'supplier_id.required' => 'Selecione um fornecedor.',
            'supplier_id.exists' => 'Selecione um fornecedor ativo válido.',
            'number.unique' => 'Já existe uma compra com esse número.',
            'purchase_date.required' => 'Informe a data da compra.',
            'expected_delivery_date.after_or_equal' =>
                'A previsão de entrega não pode ser anterior à data da compra.',
            'discount.min' => 'O desconto não pode ser negativo.',
            'items.required' => 'Adicione pelo menos um produto à compra.',
            'items.min' => 'Adicione pelo menos um produto à compra.',
            'items.*.product_id.required' => 'Selecione um produto para cada item.',
            'items.*.product_id.distinct' =>
                'Cada produto deve aparecer apenas uma vez na compra.',
            'items.*.product_id.exists' => 'Um dos produtos selecionados está inativo ou não existe.',
            'items.*.quantity.required' => 'Informe a quantidade de cada produto.',
            'items.*.quantity.gt' => 'As quantidades devem ser maiores que zero.',
            'items.*.quantity.decimal' =>
                'As quantidades podem ter até três casas decimais.',
            'items.*.unit_cost.required' => 'Informe o custo unitário de cada produto.',
            'items.*.unit_cost.decimal' =>
                'Os custos unitários podem ter até duas casas decimais.',
        ]);
    }
}