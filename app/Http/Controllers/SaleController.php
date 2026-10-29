<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $saleService
    ) {
    }

    public function index(Request $request)
    {
        $query = Sale::query()
            ->with('customer')
            ->withCount('items');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('number', 'ILIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->input('date_to'));
        }

        $sales = $query
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statuses = [
            Sale::STATUS_DRAFT => 'Rascunho',
            Sale::STATUS_COMPLETED => 'Concluída',
            Sale::STATUS_CANCELLED => 'Cancelada',
        ];

        return view('sales.index', compact('sales', 'statuses'));
    }

    public function create()
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSale($request);

        $sale = $this->saleService->create($data);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Venda salva como rascunho com sucesso.');
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
        ]);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if (!$sale->isDraft()) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'Somente vendas em rascunho podem ser editadas.');
        }

        $sale->load('items.product');

        $customers = Customer::query()
            ->where(function ($query) use ($sale) {
                $query->where('is_active', true);

                if ($sale->customer_id) {
                    $query->orWhere('id', $sale->customer_id);
                }
            })
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where(function ($query) use ($sale) {
                $query->where('is_active', true)
                    ->orWhereIn(
                        'id',
                        $sale->items->pluck('product_id')
                    );
            })
            ->orderBy('name')
            ->get();

        return view(
            'sales.edit',
            compact('sale', 'customers', 'products')
        );
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $this->validateSale($request, $sale);

        $sale = $this->saleService->update($sale, $data);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Venda atualizada com sucesso.');
    }

    
    public function cancel(Sale $sale)
    {
        $sale = $this->saleService->cancelDraft($sale);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Rascunho de venda cancelado com sucesso.');
    }


    private function validateSale(
        Request $request,
        ?Sale $sale = null
    ): array {
        return $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id'),
            ],

            'number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('sales', 'number')
                    ->ignore($sale?->id),
            ],

            'sale_date' => [
                'required',
                'date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:999999999999.99',
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

            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
        ], [
            'customer_id.exists' => 'O cliente selecionado não existe.',

            'number.unique' => 'Já existe uma venda com esse número.',

            'sale_date.required' => 'Informe a data da venda.',

            'discount.min' => 'O desconto geral não pode ser negativo.',

            'items.required' => 'Adicione pelo menos um produto à venda.',
            'items.min' => 'Adicione pelo menos um produto à venda.',

            'items.*.product_id.required' => 'Selecione um produto.',
            'items.*.product_id.exists' => 'Um produto selecionado não existe ou está inativo.',
            'items.*.product_id.distinct' => 'Não repita o mesmo produto na venda.',

            'items.*.quantity.required' => 'Informe a quantidade do produto.',
            'items.*.quantity.gt' => 'A quantidade deve ser maior que zero.',
            'items.*.quantity.decimal' => 'A quantidade deve ter no máximo três casas decimais.',

            'items.*.discount.min' => 'O desconto do item não pode ser negativo.',
            'items.*.discount.decimal' => 'O desconto do item deve ter no máximo duas casas decimais.',
        ]);
    }

    
    public function complete(Sale $sale)
    {
        $sale = $this->saleService->complete($sale);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Venda concluída e estoque atualizado com sucesso.');
    }

}