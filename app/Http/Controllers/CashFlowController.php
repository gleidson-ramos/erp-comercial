<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use App\Models\AuditLog;

class CashFlowController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from']
            ?? now()->startOfMonth()->toDateString();

        $dateTo = $validated['date_to']
            ?? now()->endOfMonth()->toDateString();

        /*
         * FLUXO REALIZADO DO PERÍODO
         */
        $baseQuery = FinancialTransaction::query()
            ->join(
                'financial_accounts',
                'financial_accounts.id',
                '=',
                'financial_transactions.financial_account_id'
            )
            ->whereBetween('financial_transactions.transaction_date', [
                $dateFrom,
                $dateTo,
            ]);

        $totalReceipts = (clone $baseQuery)
            ->where(
                'financial_accounts.type',
                FinancialAccount::TYPE_RECEIVABLE
            )
            ->sum('financial_transactions.amount');

        $totalPayments = (clone $baseQuery)
            ->where(
                'financial_accounts.type',
                FinancialAccount::TYPE_PAYABLE
            )
            ->sum('financial_transactions.amount');

        /*
         * SALDO ACUMULADO
         *
         * Considera todas as transações registradas,
         * independentemente do período selecionado.
         */
        $allTimeQuery = FinancialTransaction::query()
            ->join(
                'financial_accounts',
                'financial_accounts.id',
                '=',
                'financial_transactions.financial_account_id'
            );

        $accumulated = (clone $allTimeQuery)
            ->selectRaw('
                COALESCE(
                    SUM(
                        CASE
                            WHEN financial_accounts.type = ?
                            THEN financial_transactions.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS receipts,

                COALESCE(
                    SUM(
                        CASE
                            WHEN financial_accounts.type = ?
                            THEN financial_transactions.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS payments
            ', [
                FinancialAccount::TYPE_RECEIVABLE,
                FinancialAccount::TYPE_PAYABLE,
            ])
            ->first();

        $accumulatedReceiptsCents = $this->toCents(
            $accumulated->receipts
        );

        $accumulatedPaymentsCents = $this->toCents(
            $accumulated->payments
        );

        $balance = (
            $accumulatedReceiptsCents
            - $accumulatedPaymentsCents
        ) / 100;

        /*
         * MOVIMENTAÇÕES DIÁRIAS DO PERÍODO
         */
        $dailyMovements = (clone $baseQuery)
            ->selectRaw('
                DATE(financial_transactions.transaction_date) AS movement_date,

                SUM(
                    CASE
                        WHEN financial_accounts.type = ?
                        THEN financial_transactions.amount
                        ELSE 0
                    END
                ) AS receipts,

                SUM(
                    CASE
                        WHEN financial_accounts.type = ?
                        THEN financial_transactions.amount
                        ELSE 0
                    END
                ) AS payments
            ', [
                FinancialAccount::TYPE_RECEIVABLE,
                FinancialAccount::TYPE_PAYABLE,
            ])
            ->groupByRaw(
                'DATE(financial_transactions.transaction_date)'
            )
            ->orderByRaw(
                'DATE(financial_transactions.transaction_date)'
            )
            ->get();

        /*
         * HISTÓRICO DE MOVIMENTAÇÕES DO PERÍODO
         */
        $transactions = FinancialTransaction::query()
            ->with([
                'financialAccount.customer',
                'financialAccount.supplier',
            ])
            ->whereBetween('transaction_date', [
                $dateFrom,
                $dateTo,
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $transactionCreators = collect();

        $transactionIds = $transactions->getCollection()->modelKeys();

        if (!empty($transactionIds)) {
            $transactionCreators = AuditLog::query()
                ->where(
                    'auditable_type',
                    (new FinancialTransaction())->getMorphClass()
                )
                ->whereIn(
                    'auditable_id',
                    array_map('strval', $transactionIds)
                )
                ->where('event', 'created')
                ->get()
                ->keyBy('auditable_id');
        }

        /*
         * FLUXO PREVISTO
         *
         * Contas a receber e pagar ainda não quitadas,
         * com vencimento dentro do período selecionado.
         */
        $forecastAccounts = FinancialAccount::query()
            ->with(['customer', 'supplier'])
            ->withSum('transactions as paid_amount', 'amount')
            ->whereIn('status', [
                FinancialAccount::STATUS_OPEN,
                FinancialAccount::STATUS_PARTIAL,
            ])
            ->whereBetween('due_date', [
                $dateFrom,
                $dateTo,
            ])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->map(function (FinancialAccount $account) {
                $amountCents = $this->toCents($account->amount);
                $discountCents = $this->toCents($account->discount ?? 0);
                $interestCents = $this->toCents($account->interest ?? 0);
                $paidCents = $this->toCents($account->paid_amount ?? 0);

                $totalDueCents = $amountCents
                    - $discountCents
                    + $interestCents;

                $balanceCents = max(
                    0,
                    $totalDueCents - $paidCents
                );

                $account->pending_amount_cents = $balanceCents;
                $account->pending_amount = $balanceCents / 100;

                return $account;
            })
            ->filter(
                fn (FinancialAccount $account) =>
                    $account->pending_amount_cents > 0
            )
            ->values();

        /*
         * TOTAIS DO FLUXO PREVISTO
         */
        $totalForecastReceiptsCents = $forecastAccounts
            ->where('type', FinancialAccount::TYPE_RECEIVABLE)
            ->sum('pending_amount_cents');

        $totalForecastPaymentsCents = $forecastAccounts
            ->where('type', FinancialAccount::TYPE_PAYABLE)
            ->sum('pending_amount_cents');

        $forecastBalanceCents = $totalForecastReceiptsCents
            - $totalForecastPaymentsCents;

        /*
         * FLUXO PREVISTO AGRUPADO POR DATA
         */
        $forecastByDate = $forecastAccounts
            ->groupBy(
                fn (FinancialAccount $account) =>
                    $account->due_date->format('Y-m-d')
            )
            ->map(function (Collection $accounts, string $date) {
                return [
                    'movement_date' => $date,

                    'receipts' => $accounts
                        ->where(
                            'type',
                            FinancialAccount::TYPE_RECEIVABLE
                        )
                        ->sum('pending_amount_cents') / 100,

                    'payments' => $accounts
                        ->where(
                            'type',
                            FinancialAccount::TYPE_PAYABLE
                        )
                        ->sum('pending_amount_cents') / 100,
                ];
            })
            ->values();

        /*
         * CONTROLE DE INADIMPLÊNCIA
         *
         * Busca todas as contas vencidas, independentemente
         * do período selecionado no fluxo de caixa.
         */
        $overdueAccounts = FinancialAccount::query()
            ->with(['customer', 'supplier'])
            ->withSum('transactions as paid_amount', 'amount')
            ->whereIn('status', [
                FinancialAccount::STATUS_OPEN,
                FinancialAccount::STATUS_PARTIAL,
            ])
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->map(function (FinancialAccount $account) {
                $amountCents = $this->toCents($account->amount);
                $discountCents = $this->toCents($account->discount ?? 0);
                $interestCents = $this->toCents($account->interest ?? 0);
                $paidCents = $this->toCents($account->paid_amount ?? 0);

                $totalDueCents = $amountCents
                    - $discountCents
                    + $interestCents;

                $balanceCents = max(
                    0,
                    $totalDueCents - $paidCents
                );

                $account->pending_amount_cents = $balanceCents;
                $account->pending_amount = $balanceCents / 100;

                $account->days_overdue = (int) $account
                    ->due_date
                    ->startOfDay()
                    ->diffInDays(today(), false);

                return $account;
            })
            ->filter(
                fn (FinancialAccount $account) =>
                    $account->pending_amount_cents > 0
            )
            ->values();

        /*
         * TOTAIS DE INADIMPLÊNCIA
         */
        $totalOverdueReceivablesCents = $overdueAccounts
            ->where('type', FinancialAccount::TYPE_RECEIVABLE)
            ->sum('pending_amount_cents');

        $totalOverduePayablesCents = $overdueAccounts
            ->where('type', FinancialAccount::TYPE_PAYABLE)
            ->sum('pending_amount_cents');

        $totalOverdueCents = $totalOverdueReceivablesCents
            + $totalOverduePayablesCents;

        $overdueCount = $overdueAccounts->count();

        /*
         * RETORNO PARA A VIEW
         */
        return view('financial.cash-flow.index', compact(
            'dateFrom',
            'dateTo',
            'totalReceipts',
            'totalPayments',
            'balance',
            'dailyMovements',
            'transactions',
            'forecastAccounts',
            'totalForecastReceiptsCents',
            'totalForecastPaymentsCents',
            'forecastBalanceCents',
            'forecastByDate',
            'overdueAccounts',
            'totalOverdueReceivablesCents',
            'totalOverduePayablesCents',
            'totalOverdueCents',
            'overdueCount',
            'transactionCreators'
        ));
    }

    /**
     * Converte valores monetários para centavos.
     */
    private function toCents(mixed $value): int
    {
        return (int) round((float) $value * 100);
    }
}