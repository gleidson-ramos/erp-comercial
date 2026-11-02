<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Relatório de vendas.
     */
    public function sales(Request $request)
    {
        $validated = $request->validate([
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
        ], [
            'date_to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'customer_id.exists' => 'O cliente selecionado não foi encontrado.',
            'product_id.exists' => 'O produto selecionado não foi encontrado.',
        ]);

        $filters = [
            'date_from' => $validated['date_from']
                ?? now()->startOfMonth()->toDateString(),

            'date_to' => $validated['date_to']
                ?? now()->toDateString(),

            'customer_id' => $validated['customer_id'] ?? null,

            'product_id' => $validated['product_id'] ?? null,
        ];

        $summary = $this->reportService->salesSummary($filters);

        $customersReport = $this->reportService
            ->salesByCustomer($filters);

        $productsReport = $this->reportService
            ->bestSellingProducts($filters);

        $dailyRevenue = $this->reportService
            ->dailyRevenue($filters);

        $sales = $this->reportService
            ->recentSales($filters);

        $maxDailyRevenue = (float) $dailyRevenue
            ->max('total_revenue');

        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.sales.index', compact(
            'filters',
            'summary',
            'customersReport',
            'productsReport',
            'dailyRevenue',
            'sales',
            'maxDailyRevenue',
            'customers',
            'products'
        ));
    }


    public function financial(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', 'in:RECEBER,PAGAR'],
            'status' => ['nullable', 'in:ABERTA,PARCIAL,PAGA'],
        ]);

        $filters = [
            'date_from' => $validated['date_from']
                ?? now()->startOfMonth()->toDateString(),

            'date_to' => $validated['date_to']
                ?? now()->toDateString(),

            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        $summary = $this->reportService->financialSummary($filters);

        $accounts = $this->reportService->financialAccounts($filters);

        return view('reports.financial.index', compact(
            'filters',
            'summary',
            'accounts'
        ));
    }

    
    
    public function stock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'type' => [
                'nullable',
                'in:IN,OUT',
            ],
        ], [
            'product_id.exists' => 'O produto selecionado não foi encontrado.',
            'date_to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'type.in' => 'O tipo de movimentação selecionado é inválido.',
        ]);

        $filters = [
            'product_id' => $validated['product_id'] ?? null,
            'date_from' => $validated['date_from']
                ?? now()->startOfMonth()->toDateString(),
            'date_to' => $validated['date_to']
                ?? now()->toDateString(),
            'type' => $validated['type'] ?? null,
        ];

        // Posição atual do estoque
        $stockFilters = [
            'product_id' => $filters['product_id'],
        ];

        $summary = $this->reportService->stockSummary($stockFilters);

        $stockPosition = $this->reportService
            ->stockPosition($stockFilters);

        $lowStockProducts = $this->reportService
            ->productsBelowMinimumStock($stockFilters);

        // Histórico de movimentações
        $movementSummary = $this->reportService
            ->stockMovementSummary($filters);

        $movements = $this->reportService
            ->stockMovements($filters);

        $products = \App\Models\Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.stock.index', compact(
            'filters',
            'summary',
            'stockPosition',
            'lowStockProducts',
            'movementSummary',
            'movements',
            'products'
        ));
    }

    
    public function purchases(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $filters = [
            'date_from' => $validated['date_from']
                ?? now()->startOfMonth()->toDateString(),

            'date_to' => $validated['date_to']
                ?? now()->toDateString(),

            'supplier_id' => $validated['supplier_id'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
        ];

        $summary = $this->reportService->purchasesSummary($filters);

        $suppliersReport = $this->reportService
            ->purchasesBySupplier($filters);

        $productsReport = $this->reportService
            ->mostPurchasedProducts($filters);

        $dailyPurchases = $this->reportService
            ->dailyPurchases($filters);

        $maxDailyAmount = max(
            1,
            (float) $dailyPurchases->max('total')
        );

        $purchases = $this->reportService
            ->recentPurchases($filters);

        $suppliers = \App\Models\Supplier::query()
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        return view('reports.purchases.index', compact(
            'filters',
            'summary',
            'suppliersReport',
            'productsReport',
            'dailyPurchases',
            'maxDailyAmount',
            'purchases',
            'suppliers',
            'products'
        ));
    }

    public function index()
    {
        return view('reports.index');
    }
}