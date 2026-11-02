@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">Fluxo de Caixa</h3>
            <p class="text-muted mb-0">
                Acompanhe as entradas e saídas financeiras realizadas.
            </p>
        </div>

        <a href="{{ route('financial-accounts.index') }}"
           class="btn btn-outline-primary">
            <i class="bi bi-wallet2 me-1"></i>
            Contas financeiras
        </a>
    </div>

    {{-- Filtros --}}
    <div class="content-card mb-4">
        <form method="GET"
              action="{{ route('financial.cash-flow.index') }}">

            <div class="row g-3 align-items-end">

                <div class="col-md-4 col-lg-3">
                    <label for="date_from" class="form-label">
                        Data inicial
                    </label>

                    <input type="date"
                           name="date_from"
                           id="date_from"
                           class="form-control"
                           value="{{ $dateFrom }}">
                </div>

                <div class="col-md-4 col-lg-3">
                    <label for="date_to" class="form-label">
                        Data final
                    </label>

                    <input type="date"
                           name="date_to"
                           id="date_to"
                           class="form-control"
                           value="{{ $dateTo }}">
                </div>

                <div class="col-md-4 col-lg-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>
                        Filtrar
                    </button>

                    <a href="{{ route('financial.cash-flow.index') }}"
                       class="btn btn-outline-secondary">
                        Limpar
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Total de entradas</p>
                        <h4 class="text-success mb-0">
                            R$ {{ number_format((float) $totalReceipts, 2, ',', '.') }}
                        </h4>
                    </div>

                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-arrow-down-left text-success fs-4"></i>
                    </div>
                </div>
                <small class="text-muted">Recebimentos realizados</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Total de saídas</p>
                        <h4 class="text-danger mb-0">
                            R$ {{ number_format((float) $totalPayments, 2, ',', '.') }}
                        </h4>
                    </div>

                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-arrow-up-right text-danger fs-4"></i>
                    </div>
                </div>
                <small class="text-muted">Pagamentos realizados</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Saldo acumulado</p>
                        <h4 class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                            R$ {{ number_format((float) $balance, 2, ',', '.') }}
                        </h4>
                    </div>

                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-cash-stack text-primary fs-4"></i>
                    </div>
                </div>
                <small class="text-muted">Entradas menos saídas</small>
            </div>
        </div>

    </div>
    
    
    {{-- Fluxo previsto --}}
    <div class="mb-3 mt-5">
        <h4 class="mb-1">Fluxo previsto</h4>
        <p class="text-muted mb-0">
            Contas pendentes considerando os vencimentos do período.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <p class="text-muted mb-1">A receber</p>
                <h4 class="text-success mb-0">
                    R$
                    {{ number_format($totalForecastReceiptsCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">Saldo pendente de recebimento</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <p class="text-muted mb-1">A pagar</p>
                <h4 class="text-danger mb-0">
                    R$
                    {{ number_format($totalForecastPaymentsCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">Saldo pendente de pagamento</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="content-card h-100">
                <p class="text-muted mb-1">Saldo previsto</p>
                <h4 class="{{ $forecastBalanceCents >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    R$
                    {{ number_format($forecastBalanceCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">A receber menos a pagar</small>
            </div>
        </div>

    </div>

    {{-- Previsão por vencimento --}}
    <div class="content-card mb-4">

        <div class="mb-3">
            <h5 class="mb-1">Previsão por vencimento</h5>
            <small class="text-muted">
                Valores pendentes agrupados pela data de vencimento.
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th class="text-end">A receber</th>
                        <th class="text-end">A pagar</th>
                        <th class="text-end">Saldo previsto</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($forecastByDate as $day)
                        @php
                            $dailyBalance = $day['receipts'] - $day['payments'];
                        @endphp

                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($day['movement_date'])->format('d/m/Y') }}
                            </td>

                            <td class="text-end text-success">
                                R$ {{ number_format($day['receipts'], 2, ',', '.') }}
                            </td>

                            <td class="text-end text-danger">
                                R$ {{ number_format($day['payments'], 2, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold {{ $dailyBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($dailyBalance, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                Nenhuma conta pendente com vencimento no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>


    {{-- Comparativo entre previsto e realizado --}}
    @php
        /*
        * COMPARATIVO ENTRE PREVISTO E REALIZADO
        */

        $comparisonByDate = collect();

        foreach ($dailyMovements as $movement) {
            $date = \Carbon\Carbon::parse(
                $movement->movement_date
            )->format('Y-m-d');

            $comparisonByDate->put($date, [
                'date' => $date,
                'forecast_receipts' => 0,
                'forecast_payments' => 0,
                'realized_receipts' => (float) $movement->receipts,
                'realized_payments' => (float) $movement->payments,
            ]);
        }

        foreach ($forecastByDate as $forecast) {
            $date = $forecast['movement_date'];

            $day = $comparisonByDate->get($date, [
                'date' => $date,
                'forecast_receipts' => 0,
                'forecast_payments' => 0,
                'realized_receipts' => 0,
                'realized_payments' => 0,
            ]);

            $day['forecast_receipts'] = (float) $forecast['receipts'];
            $day['forecast_payments'] = (float) $forecast['payments'];

            $comparisonByDate->put($date, $day);
        }

        $comparisonByDate = $comparisonByDate
            ->sortKeys()
            ->values();
    @endphp

    <div class="content-card mb-4">

        <div class="mb-3">
            <h5 class="mb-1">Comparativo: previsto x realizado</h5>
            <small class="text-muted">
                Compare os valores previstos com as movimentações efetivamente registradas em cada dia.
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">Data</th>
                        <th colspan="2" class="text-center">Entradas</th>
                        <th colspan="2" class="text-center">Saídas</th>
                        <th rowspan="2" class="text-end align-middle">
                            Diferença do saldo
                        </th>
                    </tr>
                    <tr>
                        <th class="text-end">Previsto</th>
                        <th class="text-end">Realizado</th>
                        <th class="text-end">Previsto</th>
                        <th class="text-end">Realizado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($comparisonByDate as $day)
                        @php
                            $forecastBalance =
                                $day['forecast_receipts']
                                - $day['forecast_payments'];

                            $realizedBalance =
                                $day['realized_receipts']
                                - $day['realized_payments'];

                            $balanceDifference =
                                $realizedBalance - $forecastBalance;
                        @endphp

                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                            </td>

                            <td class="text-end text-muted">
                                R$ {{ number_format($day['forecast_receipts'], 2, ',', '.') }}
                            </td>

                            <td class="text-end text-success fw-semibold">
                                R$ {{ number_format($day['realized_receipts'], 2, ',', '.') }}
                            </td>

                            <td class="text-end text-muted">
                                R$ {{ number_format($day['forecast_payments'], 2, ',', '.') }}
                            </td>

                            <td class="text-end text-danger fw-semibold">
                                R$ {{ number_format($day['realized_payments'], 2, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold {{ $balanceDifference >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $balanceDifference > 0 ? '+' : '' }}
                                R$ {{ number_format($balanceDifference, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Não existem dados para comparação no período selecionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                A diferença representa o saldo realizado menos o saldo previsto.
            </small>
        </div>

    </div>

    {{-- Contas pendentes --}}
    <div class="content-card mb-4">

        <div class="mb-3">
            <h5 class="mb-1">Contas pendentes</h5>
            <small class="text-muted">
                Contas abertas ou parcialmente pagas no período selecionado.
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Descrição</th>
                        <th>Cliente / Fornecedor</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor original</th>
                        <th class="text-end">Saldo pendente</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($forecastAccounts as $account)
                        @php
                            $isReceivable = $account->type ===
                                \App\Models\FinancialAccount::TYPE_RECEIVABLE;

                            $relatedParty = $account->customer?->name
                                ?? $account->supplier?->fantasy_name
                                ?? $account->supplier?->name
                                ?? '-';
                        @endphp

                        <tr>
                            <td>
                                {{ $account->due_date->format('d/m/Y') }}

                                @if($account->due_date->isPast() && !$account->due_date->isToday())
                                    <span class="badge bg-danger ms-1">Vencida</span>
                                @endif
                            </td>

                            <td>{{ $account->description }}</td>

                            <td>{{ $relatedParty }}</td>

                            <td>
                                @if($isReceivable)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        A receber
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        A pagar
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                R$ {{ number_format((float) $account->amount, 2, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold">
                                R$ {{ number_format($account->pending_amount, 2, ',', '.') }}
                            </td>

                            <td class="text-center">
                                <a href="{{ route('financial-accounts.show', $account) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Visualizar conta">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Nenhuma conta pendente encontrada no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @php
        /*
        * COMPARATIVO ENTRE FLUXO PREVISTO E REALIZADO
        */

        $comparisonByDate = collect();

        // Movimentações efetivamente realizadas.
        foreach ($dailyMovements as $movement) {
            $date = \Carbon\Carbon::parse(
                $movement->movement_date
            )->format('Y-m-d');

            $comparisonByDate->put($date, [
                'date' => $date,
                'forecast_receipts' => 0,
                'forecast_payments' => 0,
                'realized_receipts' => (float) $movement->receipts,
                'realized_payments' => (float) $movement->payments,
            ]);
        }

        // Valores previstos com base nos vencimentos.
        foreach ($forecastByDate as $forecast) {
            $date = $forecast['movement_date'];

            $day = $comparisonByDate->get($date, [
                'date' => $date,
                'forecast_receipts' => 0,
                'forecast_payments' => 0,
                'realized_receipts' => 0,
                'realized_payments' => 0,
            ]);

            $day['forecast_receipts'] = (float) $forecast['receipts'];
            $day['forecast_payments'] = (float) $forecast['payments'];

            $comparisonByDate->put($date, $day);
        }

        $comparisonByDate = $comparisonByDate
            ->sortKeys()
            ->values();
    @endphp

    {{-- Movimentações diárias --}}
    <div class="content-card mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h5 class="mb-1">Movimentações diárias</h5>
                <small class="text-muted">
                    Entradas e saídas por data de movimentação.
                </small>
            </div>

            <div class="d-flex gap-3">
                <span class="small">
                    <i class="bi bi-circle-fill text-success me-1"></i>
                    Entradas
                </span>

                <span class="small">
                    <i class="bi bi-circle-fill text-danger me-1"></i>
                    Saídas
                </span>
            </div>
        </div>

        @php
            $maxMovement = max(
                1,
                (float) $dailyMovements->max('receipts'),
                (float) $dailyMovements->max('payments')
            );
        @endphp

        @if($dailyMovements->isEmpty())

            <div class="text-center py-5">
                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">
                    Nenhuma movimentação encontrada no período.
                </p>
            </div>

        @else

            <div class="d-flex align-items-end gap-2 overflow-x-auto pb-3"
                 style="min-height: 230px;">

                @foreach($dailyMovements as $movement)

                    @php
                        $receiptHeight = max(
                            2,
                            ((float) $movement->receipts / $maxMovement) * 150
                        );

                        $paymentHeight = max(
                            2,
                            ((float) $movement->payments / $maxMovement) * 150
                        );
                    @endphp

                    <div class="d-flex flex-column align-items-center justify-content-end"
                         style="min-width: 55px; height: 190px;">

                        <div class="d-flex align-items-end gap-1"
                             style="height: 160px;">

                            <div class="bg-success rounded-top"
                                 title="Entradas: R$ {{ number_format((float) $movement->receipts, 2, ',', '.') }}"
                                 style="width: 15px; height: {{ $receiptHeight }}px;">
                            </div>

                            <div class="bg-danger rounded-top"
                                 title="Saídas: R$ {{ number_format((float) $movement->payments, 2, ',', '.') }}"
                                 style="width: 15px; height: {{ $paymentHeight }}px;">
                            </div>

                        </div>

                        <small class="text-muted mt-2">
                            {{ \Carbon\Carbon::parse($movement->movement_date)->format('d/m') }}
                        </small>

                    </div>

                @endforeach

            </div>

        @endif
    </div>

    
    {{-- Contas vencidas --}}
    <div class="mt-5 mb-3">
        <h4 class="mb-1">Inadimplência e contas vencidas</h4>
        <p class="text-muted mb-0">
            Acompanhamento de valores pendentes com vencimento anterior a hoje.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="content-card h-100">
                <p class="text-muted mb-1">Vencido a receber</p>
                <h4 class="text-success mb-0">
                    R$ {{ number_format($totalOverdueReceivablesCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">Valores pendentes de clientes</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="content-card h-100">
                <p class="text-muted mb-1">Vencido a pagar</p>
                <h4 class="text-danger mb-0">
                    R$ {{ number_format($totalOverduePayablesCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">Valores pendentes com fornecedores</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="content-card h-100">
                <p class="text-muted mb-1">Total vencido</p>
                <h4 class="mb-0">
                    R$ {{ number_format($totalOverdueCents / 100, 2, ',', '.') }}
                </h4>
                <small class="text-muted">Soma de contas a pagar e receber</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="content-card h-100">
                <p class="text-muted mb-1">Contas vencidas</p>
                <h4 class="mb-0">{{ $overdueCount }}</h4>
                <small class="text-muted">Contas com saldo pendente</small>
            </div>
        </div>

    </div>

    {{-- Lista de contas vencidas --}}
    <div class="content-card mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Contas em atraso</h5>
                <small class="text-muted">
                    A lista não depende do período selecionado nos filtros do fluxo de caixa.
                </small>
            </div>

            <span class="badge bg-danger">
                {{ $overdueCount }} vencidas
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Descrição</th>
                        <th>Cliente / Fornecedor</th>
                        <th>Tipo</th>
                        <th>Dias em atraso</th>
                        <th class="text-end">Saldo pendente</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($overdueAccounts as $account)

                        @php
                            $isReceivable = $account->type ===
                                \App\Models\FinancialAccount::TYPE_RECEIVABLE;

                            $relatedParty = $account->customer?->name
                                ?? $account->supplier?->fantasy_name
                                ?? $account->supplier?->name
                                ?? '-';
                        @endphp

                        <tr>
                            <td>
                                {{ $account->due_date->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $account->description }}
                            </td>

                            <td>
                                {{ $relatedParty }}
                            </td>

                            <td>
                                @if($isReceivable)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        A receber
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        A pagar
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="text-danger fw-semibold">
                                    {{ $account->days_overdue }}
                                    {{ $account->days_overdue === 1 ? 'dia' : 'dias' }}
                                </span>
                            </td>

                            <td class="text-end fw-semibold">
                                R$ {{ number_format($account->pending_amount, 2, ',', '.') }}
                            </td>

                            <td class="text-center">
                                <a href="{{ route('financial-accounts.show', $account) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Visualizar conta">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check-circle fs-2 text-success"></i>

                                <p class="text-muted mt-2 mb-0">
                                    Não existem contas vencidas com saldo pendente.
                                </p>
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

    {{-- Histórico --}}
    <div class="content-card">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="mb-1">Histórico de movimentações</h5>
                <small class="text-muted">
                    Transações registradas no período selecionado.
                </small>
            </div>

            <span class="badge bg-light text-dark">
                {{ $transactions->total() }} registros
            </span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Cliente / Fornecedor</th>
                        <th>Forma de pagamento</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($transactions as $transaction)

                        @php
                            $account = $transaction->financialAccount;

                            $isReceipt = $account?->type ===
                                \App\Models\FinancialAccount::TYPE_RECEIVABLE;

                            $relatedParty = $account?->customer?->name
                                ?? $account?->supplier?->fantasy_name
                                ?? $account?->supplier?->name
                                ?? '-';
                        @endphp

                        <tr>
                            <td>
                                {{ $transaction->transaction_date->format('d/m/Y') }}
                            </td>

                            <td>
                                <div class="fw-medium">
                                    {{ $account?->description ?? 'Conta financeira' }}
                                </div>

                                @if($transaction->reference)
                                    <small class="text-muted">
                                        {{ $transaction->reference }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $relatedParty }}
                            </td>

                            <td>
                                {{ $transaction->payment_method }}
                            </td>

                            <td>
                                @if($isReceipt)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        Recebimento
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        Pagamento
                                    </span>
                                @endif
                            </td>

                            <td class="text-end fw-semibold {{ $isReceipt ? 'text-success' : 'text-danger' }}">
                                {{ $isReceipt ? '+' : '−' }}
                                R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 text-muted"></i>

                                <p class="text-muted mb-0 mt-2">
                                    Nenhuma movimentação encontrada.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $transactions->links() }}
        </div>

    </div>

</div>
@endsection