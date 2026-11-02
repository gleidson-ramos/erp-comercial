<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Aplica os filtros comuns aos relatórios de vendas.
     */
    private function completedSalesQuery(array $filters): Builder
    {
        $query = Sale::query()
            ->where('status', 'CONCLUIDA')
            ->whereBetween('sale_date', [
                $filters['date_from'],
                $filters['date_to'],
            ]);

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['product_id'])) {
            $productId = $filters['product_id'];

            $query->whereHas('items', function ($itemQuery) use ($productId) {
                $itemQuery->where('product_id', $productId);
            });
        }

        return $query;
    }

    /**
     * Resumo geral das vendas.
     */
    public function salesSummary(array $filters): array
    {
        $summary = $this->completedSalesQuery($filters)
            ->selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(AVG(total), 0) as average_ticket
            ')
            ->first();

        return [
            'total_sales' => (int) $summary->total_sales,
            'total_revenue' => (float) $summary->total_revenue,
            'total_discount' => (float) $summary->total_discount,
            'average_ticket' => (float) $summary->average_ticket,
        ];
    }

    /**
     * Vendas agrupadas por cliente.
     */
    public function salesByCustomer(array $filters)
    {
        return $this->completedSalesQuery($filters)
            ->selectRaw('
                customer_id,
                COUNT(*) as sales_count,
                COALESCE(SUM(total), 0) as total_revenue
            ')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_revenue')
            ->limit(15)
            ->get();
    }

    /**
     * Produtos mais vendidos.
     */
    public function bestSellingProducts(array $filters)
    {
        $query = SaleItem::query()
            ->whereHas('sale', function ($saleQuery) use ($filters) {
                $saleQuery
                    ->where('status', 'CONCLUIDA')
                    ->whereBetween('sale_date', [
                        $filters['date_from'],
                        $filters['date_to'],
                    ]);

                if (!empty($filters['customer_id'])) {
                    $saleQuery->where(
                        'customer_id',
                        $filters['customer_id']
                    );
                }
            })
            ->with('product')
            ->selectRaw('
                product_id,
                COALESCE(SUM(quantity), 0) as total_quantity,
                COALESCE(SUM(subtotal), 0) as total_revenue,
                COUNT(DISTINCT sale_id) as sales_count
            ');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(15)
            ->get();
    }

    /**
     * Faturamento agrupado por dia.
     */
    public function dailyRevenue(array $filters)
    {
        return $this->completedSalesQuery($filters)
            ->selectRaw('
                sale_date,
                COUNT(*) as sales_count,
                COALESCE(SUM(total), 0) as total_revenue
            ')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();
    }

    /**
     * Últimas vendas do período.
     */
    public function recentSales(array $filters)
    {
        return $this->completedSalesQuery($filters)
            ->with('customer')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    
    /**
     * Aplica os filtros comuns aos relatórios de compras.
     */
    private function purchasesQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = \App\Models\Purchase::query()
            ->whereNotIn('status', [
                \App\Models\Purchase::STATUS_DRAFT,
                \App\Models\Purchase::STATUS_CANCELLED,
            ])
            ->whereBetween('purchase_date', [
                $filters['date_from'],
                $filters['date_to'],
            ]);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['product_id'])) {
            $productId = $filters['product_id'];

            $query->whereHas('items', function ($itemQuery) use ($productId) {
                $itemQuery->where('product_id', $productId);
            });
        }

        return $query;
    }

    /**
     * Resumo geral das compras.
     */
    public function purchasesSummary(array $filters): array
    {
        $summary = $this->purchasesQuery($filters)
            ->selectRaw('
                COUNT(*) as total_purchases,
                COALESCE(SUM(total), 0) as total_amount,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(AVG(total), 0) as average_ticket
            ')
            ->first();

        return [
            'total_purchases' => (int) $summary->total_purchases,
            'total_amount' => (float) $summary->total_amount,
            'total_discount' => (float) $summary->total_discount,
            'average_ticket' => (float) $summary->average_ticket,
        ];
    }

    /**
     * Compras agrupadas por fornecedor.
     */
    public function purchasesBySupplier(array $filters)
    {
        return $this->purchasesQuery($filters)
            ->selectRaw('
                supplier_id,
                COUNT(*) as purchases_count,
                COALESCE(SUM(total), 0) as total_amount
            ')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->limit(15)
            ->get();
    }

    /**
     * Produtos mais comprados.
     */
    public function mostPurchasedProducts(array $filters)
    {
        $query = \App\Models\PurchaseItem::query()
            ->whereHas('purchase', function ($purchaseQuery) use ($filters) {
                $purchaseQuery
                    ->whereNotIn('status', [
                        \App\Models\Purchase::STATUS_DRAFT,
                        \App\Models\Purchase::STATUS_CANCELLED,
                    ])
                    ->whereBetween('purchase_date', [
                        $filters['date_from'],
                        $filters['date_to'],
                    ]);

                if (!empty($filters['supplier_id'])) {
                    $purchaseQuery->where(
                        'supplier_id',
                        $filters['supplier_id']
                    );
                }
            })
            ->with('product')
            ->selectRaw('
                product_id,
                COALESCE(SUM(quantity), 0) as total_quantity,
                COALESCE(SUM(subtotal), 0) as total_amount,
                COUNT(DISTINCT purchase_id) as purchases_count
            ');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(15)
            ->get();
    }

    /**
     * Compras agrupadas por dia.
     */
    public function dailyPurchases(array $filters)
    {
        return $this->purchasesQuery($filters)
            ->selectRaw('
                purchase_date,
                COUNT(*) as purchases_count,
                COALESCE(SUM(total), 0) as total_amount
            ')
            ->groupBy('purchase_date')
            ->orderBy('purchase_date')
            ->get();
    }

    /**
     * Histórico de compras.
     */
    public function recentPurchases(array $filters)
    {
        return $this->purchasesQuery($filters)
            ->with('supplier')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    private function financialAccountsQuery(array $filters)
    {
        $query = \App\Models\FinancialAccount::query()
            ->with(['customer', 'supplier'])
            ->withSum('transactions as paid_amount', 'amount')
            ->whereBetween('due_date', [
                $filters['date_from'],
                \Carbon\Carbon::parse($filters['date_to'])->endOfDay(),
            ])
            ->where('status', '!=', 'CANCELADA');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function financialSummary(array $filters): array
    {
        $accounts = $this->financialAccountsQuery($filters)->get();

        $totalReceivable = 0;
        $totalPayable = 0;
        $received = 0;
        $paid = 0;
        $overdueReceivable = 0;
        $overduePayable = 0;
        $partialCount = 0;
        $overdueCount = 0;

        foreach ($accounts as $account) {
            $amount = (float) $account->amount;
            $paidAmount = (float) ($account->paid_amount ?? 0);

            $pending = max(0, $amount - $paidAmount);

            if ($account->type === 'RECEBER') {
                $totalReceivable += $amount;
                $received += $paidAmount;

                if ($account->due_date < now()->startOfDay() && $pending > 0) {
                    $overdueReceivable += $pending;
                    $overdueCount++;
                }
            }

            if ($account->type === 'PAGAR') {
                $totalPayable += $amount;
                $paid += $paidAmount;

                if ($account->due_date < now()->startOfDay() && $pending > 0) {
                    $overduePayable += $pending;
                    $overdueCount++;
                }
            }

            if ($account->status === 'PARCIAL') {
                $partialCount++;
            }
        }

        return [
            'total_receivable' => $totalReceivable,
            'total_payable' => $totalPayable,
            'received' => $received,
            'paid' => $paid,
            'pending_receivable' => max(0, $totalReceivable - $received),
            'pending_payable' => max(0, $totalPayable - $paid),
            'overdue_receivable' => $overdueReceivable,
            'overdue_payable' => $overduePayable,
            'partial_count' => $partialCount,
            'overdue_count' => $overdueCount,
            'accounts_count' => $accounts->count(),
        ];
    }

    public function financialAccounts(array $filters)
    {
        return $this->financialAccountsQuery($filters)
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
    }

    
    /**
     * Consulta o histórico de movimentações de estoque.
     */
    private function stockMovementsQuery(array $filters)
    {
        $query = \App\Models\StockMovement::query()
            ->with('product')
            ->whereBetween('moved_at', [
                $filters['date_from'],
                \Carbon\Carbon::parse($filters['date_to'])->endOfDay(),
            ]);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    /**
     * Indicadores das movimentações no período.
     */
    public function stockMovementSummary(array $filters): array
    {
        $query = $this->stockMovementsQuery($filters);

        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total_movements,
                COALESCE(SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END), 0) as total_entries,
                COALESCE(SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END), 0) as total_exits
            ")
            ->first();

        return [
            'total_movements' => (int) $summary->total_movements,
            'total_entries' => (float) $summary->total_entries,
            'total_exits' => (float) $summary->total_exits,
        ];
    }

    /**
     * Lista paginada das movimentações.
     */
    public function stockMovements(array $filters)
    {
        return $this->stockMovementsQuery($filters)
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();
    }

    
    /**
     * Consulta a posição atual do estoque.
     */
    private function stockPositionQuery(array $filters)
    {
        $query = \App\Models\InventoryStock::query()
            ->with(['product.unit'])
            ->whereHas('product');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query;
    }

    /**
     * Produtos com estoque igual ou abaixo do mínimo.
     */
    private function lowStockQuery(array $filters)
    {
        $query = $this->stockPositionQuery($filters);

        $query->whereHas('product', function ($productQuery) {
            $productQuery
                ->whereNotNull('minimum_stock')
                ->whereColumn(
                    'inventory_stocks.quantity',
                    '<=',
                    'products.minimum_stock'
                );
        });

        return $query;
    }

    /**
     * Indicadores gerais do estoque.
     */
    public function stockSummary(array $filters): array
    {
        $position = $this->stockPositionQuery($filters);

        $lowStock = $this->lowStockQuery($filters);

        return [
            'products_count' => (clone $position)->count(),
            'total_quantity' => (float) (clone $position)->sum('quantity'),
            'low_stock_count' => (clone $lowStock)->count(),
        ];
    }

    /**
     * Posição atual do estoque.
     */
    public function stockPosition(array $filters)
    {
        return $this->stockPositionQuery($filters)
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Produtos abaixo ou no estoque mínimo.
     */
    public function productsBelowMinimumStock(array $filters)
    {
        return $this->lowStockQuery($filters)
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Todos os registros de vendas para exportação.
     */
    public function salesForExport(array $filters)
    {
        return $this->completedSalesQuery($filters)
            ->with('customer')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Todos os registros de compras para exportação.
     */
    public function purchasesForExport(array $filters)
    {
        return $this->purchasesQuery($filters)
            ->with('supplier')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Todas as contas financeiras para exportação.
     */
    public function financialAccountsForExport(array $filters)
    {
        return $this->financialAccountsQuery($filters)
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * Todas as movimentações de estoque para exportação.
     */
    public function stockMovementsForExport(array $filters)
    {
        return $this->stockMovementsQuery($filters)
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Posição completa do estoque para exportação.
     */
    public function stockPositionForExport(array $filters)
    {
        return $this->stockPositionQuery($filters)
            ->orderBy('product_id')
            ->get();
    }

    /**
     * Todos os produtos abaixo do estoque mínimo para exportação.
     */
    public function productsBelowMinimumStockForExport(array $filters)
    {
        return $this->lowStockQuery($filters)
            ->orderBy('product_id')
            ->get();
    }
}