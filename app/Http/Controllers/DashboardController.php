<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filtros de período
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'period' => [
                'nullable',
                'in:today,last_7_days,last_30_days,this_month,this_year,custom',
            ],
            'start_date' => [
                'required_if:period,custom',
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'end_date' => [
                'required_if:period,custom',
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                'before_or_equal:today',
            ],
        ]);

        $today = Carbon::today();

        $period = $validated['period'] ?? 'this_month';

        switch ($period) {
            case 'today':
                $startDate = $today->copy();
                $endDate = $today->copy();
                break;

            case 'last_7_days':
                $startDate = $today->copy()->subDays(6);
                $endDate = $today->copy();
                break;

            case 'last_30_days':
                $startDate = $today->copy()->subDays(29);
                $endDate = $today->copy();
                break;

            case 'this_year':
                $startDate = $today->copy()->startOfYear();
                $endDate = $today->copy();
                break;

            case 'custom':
                $startDate = Carbon::parse($validated['start_date']);
                $endDate = Carbon::parse($validated['end_date']);
                break;

            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy();
                break;
        }

        $startDateString = $startDate->toDateString();
        $endDateString = $endDate->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Cadastros
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $activeProducts = Product::where('is_active', true)->count();

        $totalCustomers = Customer::count();

        /*
        |--------------------------------------------------------------------------
        | Estoque
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = Product::query()
            ->with(['inventoryStock', 'unit'])
            ->where('is_active', true)
            ->where('minimum_stock', '>', 0)
            ->whereHas('inventoryStock', function ($query) {
                $query->whereColumn(
                    'inventory_stocks.quantity',
                    '<=',
                    'products.minimum_stock'
                );
            })
            ->orderBy('name')
            ->get();

        $lowStockCount = $lowStockProducts->count();

        /*
        |--------------------------------------------------------------------------
        | Vendas
        |--------------------------------------------------------------------------
        */

        $salesQuery = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sale_date', [
                $startDateString,
                $endDateString,
            ]);

        $salesCount = (clone $salesQuery)->count();
        $salesTotal = (clone $salesQuery)->sum('total');

        /*
        |--------------------------------------------------------------------------
        | Compras
        |--------------------------------------------------------------------------
        */

        $purchaseQuery = Purchase::query()
            ->whereIn('status', [
                Purchase::STATUS_CONFIRMED,
                Purchase::STATUS_PARTIAL,
                Purchase::STATUS_RECEIVED,
            ])
            ->whereBetween('purchase_date', [
                $startDateString,
                $endDateString,
            ]);

        $purchasesCount = (clone $purchaseQuery)->count();
        $purchasesTotal = (clone $purchaseQuery)->sum('total');

        $commercialBalance = (float) $salesTotal - (float) $purchasesTotal;

        
        /*
        |--------------------------------------------------------------------------
        | Comparação com o período anterior
        |--------------------------------------------------------------------------
        */

        // Quantidade de dias do período selecionado, incluindo as extremidades.
        $periodDays = (int) $startDate->diffInDays($endDate) + 1;

        // Calcula o período imediatamente anterior, com a mesma duração.
        $previousEndDate = $startDate->copy()->subDay();

        $previousStartDate = $previousEndDate
            ->copy()
            ->subDays($periodDays - 1);

        $previousStartDateString = $previousStartDate->toDateString();
        $previousEndDateString = $previousEndDate->toDateString();

        // Vendas do período anterior.
        $previousSalesQuery = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sale_date', [
                $previousStartDateString,
                $previousEndDateString,
            ]);

        $previousSalesCount = (clone $previousSalesQuery)->count();
        $previousSalesTotal = (float) (clone $previousSalesQuery)->sum('total');

        // Compras do período anterior.
        $previousPurchaseQuery = Purchase::query()
            ->whereIn('status', [
                Purchase::STATUS_CONFIRMED,
                Purchase::STATUS_PARTIAL,
                Purchase::STATUS_RECEIVED,
            ])
            ->whereBetween('purchase_date', [
                $previousStartDateString,
                $previousEndDateString,
            ]);

        $previousPurchasesCount = (clone $previousPurchaseQuery)->count();
        $previousPurchasesTotal = (float) (clone $previousPurchaseQuery)->sum('total');

        // Calcula a variação percentual.
        // Quando o período anterior não possui movimentação, retorna null
        // se o período atual possuir valor.
        $calculateVariation = function ($current, $previous) {
            if ((float) $previous === 0.0) {
                return (float) $current === 0.0
                    ? 0.0
                    : null;
            }

            return (($current - $previous) / abs($previous)) * 100;
        };

        $salesVariation = $calculateVariation(
            (float) $salesTotal,
            $previousSalesTotal
        );

        $purchasesVariation = $calculateVariation(
            (float) $purchasesTotal,
            $previousPurchasesTotal
        );

        /*
        |--------------------------------------------------------------------------
        | Financeiro
        |--------------------------------------------------------------------------
        */

        $pendingAccounts = FinancialAccount::query()
            ->withSum('transactions as paid_amount', 'amount')
            ->whereIn('status', [
                FinancialAccount::STATUS_OPEN,
                FinancialAccount::STATUS_PARTIAL,
            ])
            ->get();

        $totalReceivableCents = 0;
        $totalPayableCents = 0;

        foreach ($pendingAccounts as $account) {
            $amountCents = (int) round((float) $account->amount * 100);
            $discountCents = (int) round((float) $account->discount * 100);
            $interestCents = (int) round((float) $account->interest * 100);
            $paidCents = (int) round((float) ($account->paid_amount ?? 0) * 100);

            $balanceCents = max(
                0,
                $amountCents - $discountCents + $interestCents - $paidCents
            );

            if ($account->type === FinancialAccount::TYPE_RECEIVABLE) {
                $totalReceivableCents += $balanceCents;
            }

            if ($account->type === FinancialAccount::TYPE_PAYABLE) {
                $totalPayableCents += $balanceCents;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Contas vencidas
        |--------------------------------------------------------------------------
        */

        $overdueAccounts = $pendingAccounts
            ->filter(function ($account) use ($today) {
                return $account->due_date
                    && $account->due_date->lt($today);
            })
            ->map(function ($account) {
                $amountCents = (int) round((float) $account->amount * 100);
                $discountCents = (int) round((float) $account->discount * 100);
                $interestCents = (int) round((float) $account->interest * 100);
                $paidCents = (int) round((float) ($account->paid_amount ?? 0) * 100);

                $account->pending_amount_cents = max(
                    0,
                    $amountCents - $discountCents + $interestCents - $paidCents
                );

                return $account;
            })
            ->filter(fn ($account) => $account->pending_amount_cents > 0)
            ->values();

        $overdueCount = $overdueAccounts->count();

        $overdueTotalCents = $overdueAccounts->sum(
            'pending_amount_cents'
        );

        /*
        |--------------------------------------------------------------------------
        | Gráfico de vendas e compras por dia
        |--------------------------------------------------------------------------
        */

        $salesByDay = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sale_date', [
                $startDateString,
                $endDateString,
            ])
            ->selectRaw('DATE(sale_date) as date, SUM(total) as total')
            ->groupByRaw('DATE(sale_date)')
            ->pluck('total', 'date');

        $purchasesByDay = Purchase::query()
            ->whereIn('status', [
                Purchase::STATUS_CONFIRMED,
                Purchase::STATUS_PARTIAL,
                Purchase::STATUS_RECEIVED,
            ])
            ->whereBetween('purchase_date', [
                $startDateString,
                $endDateString,
            ])
            ->selectRaw('DATE(purchase_date) as date, SUM(total) as total')
            ->groupByRaw('DATE(purchase_date)')
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartSales = [];
        $chartPurchases = [];

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->toDateString();

            $chartLabels[] = $currentDate->format('d/m');

            $chartSales[] = (float) ($salesByDay[$dateKey] ?? 0);

            $chartPurchases[] = (float) ($purchasesByDay[$dateKey] ?? 0);

            $currentDate->addDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Produtos mais vendidos
        |--------------------------------------------------------------------------
        */

        $bestSellingProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sale_date', [
                $startDateString,
                $endDateString,
            ])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Últimas vendas
        |--------------------------------------------------------------------------
        */

        $recentSales = Sale::query()
            ->with('customer')
            ->where('status', Sale::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Últimas compras
        |--------------------------------------------------------------------------
        */

        $recentPurchases = Purchase::query()
            ->with('supplier')
            ->whereIn('status', [
                Purchase::STATUS_CONFIRMED,
                Purchase::STATUS_PARTIAL,
                Purchase::STATUS_RECEIVED,
            ])
            ->orderByDesc('purchase_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'today',
            'period',
            'startDate',
            'endDate',
            'totalProducts',
            'activeProducts',
            'totalCustomers',
            'lowStockProducts',
            'lowStockCount',
            'salesCount',
            'salesTotal',
            'purchasesCount',
            'purchasesTotal',
            'totalReceivableCents',
            'totalPayableCents',
            'overdueAccounts',
            'overdueCount',
            'overdueTotalCents',
            'chartLabels',
            'chartSales',
            'chartPurchases',
            'bestSellingProducts',
            'recentSales',
            'recentPurchases',
            'commercialBalance',
            'previousStartDate',
            'previousEndDate',
            'previousSalesCount',
            'previousSalesTotal',
            'previousPurchasesCount',
            'previousPurchasesTotal',
            'salesVariation',
            'purchasesVariation'
        ));
    }
}