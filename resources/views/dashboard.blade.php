@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $currency = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');

    $currencyFromCents = fn ($value) => $currency(($value ?? 0) / 100);

    $periodOptions = [
        'today' => 'Hoje',
        'last_7_days' => 'Últimos 7 dias',
        'last_30_days' => 'Últimos 30 dias',
        'this_month' => 'Este mês',
        'this_year' => 'Este ano',
        'custom' => 'Período personalizado',
    ];

    $maxChartValue = max(
        1,
        ...array_merge($chartSales, $chartPurchases)
    );

    $chartHasData = collect($chartSales)
        ->merge($chartPurchases)
        ->contains(fn ($value) => (float) $value > 0);
@endphp

<div class="container-fluid">

    {{-- Cabeçalho --}}
    <div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">
                Visão geral da operação comercial.
            </p>
        </div>

        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>
            {{ $startDate->format('d/m/Y') }}
            até
            {{ $endDate->format('d/m/Y') }}
        </div>
    </div>

    {{-- Boas-vindas --}}
    <div class="welcome-panel mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h4 class="mb-2">Bem-vindo ao seu ERP!</h4>
                <p class="mb-0">
                    Acompanhe as principais informações do seu negócio,
                    gerencie suas operações e monitore seus resultados.
                </p>
            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-light text-primary px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i>
                    Sistema operacional
                </span>
            </div>
        </div>
    </div>

    {{-- Filtros de período --}}
    <div class="content-card mb-4">
        <div class="section-heading mb-3">
            <div>
                <h5 class="mb-1">Período de análise</h5>
                <p class="text-muted small mb-0">
                    Selecione o intervalo para consultar vendas e compras.
                </p>
            </div>
        </div>

        <form action="{{ route('dashboard') }}" method="GET" id="dashboard-filter-form">
            <div class="row align-items-end g-3">

                <div class="col-md-4 col-lg-3">
                    <label for="period" class="form-label">
                        Período
                    </label>

                    <select
                        name="period"
                        id="period"
                        class="form-select"
                    >
                        @foreach($periodOptions as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($period === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div
                    class="col-md-3 col-lg-3 custom-date-field"
                    id="start-date-container"
                >
                    <label for="start_date" class="form-label">
                        Data inicial
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control"
                        value="{{ $startDate->format('Y-m-d') }}"
                        max="{{ $today->format('Y-m-d') }}"
                    >
                </div>

                <div
                    class="col-md-3 col-lg-3 custom-date-field"
                    id="end-date-container"
                >
                    <label for="end_date" class="form-label">
                        Data final
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control"
                        value="{{ $endDate->format('Y-m-d') }}"
                        max="{{ $today->format('Y-m-d') }}"
                    >
                </div>

                <div class="col-md-2 col-lg-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>
                        Aplicar filtros
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Indicadores principais --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Produtos cadastrados</p>
                        <h3 class="mb-1">{{ number_format($totalProducts, 0, ',', '.') }}</h3>
                        <small class="text-muted">
                            {{ $activeProducts }} ativos
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Clientes cadastrados</p>
                        <h3 class="mb-1">{{ number_format($totalCustomers, 0, ',', '.') }}</h3>
                        <small class="text-muted">Base de clientes</small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Vendas no período</p>
                        <h3 class="mb-1">{{ number_format($salesCount, 0, ',', '.') }}</h3>
                        <small class="text-success">
                            {{ $currency($salesTotal) }}
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Produtos com estoque baixo</p>
                        <h3 class="mb-1">{{ number_format($lowStockCount, 0, ',', '.') }}</h3>
                        <small class="text-warning">
                            Necessitam de atenção
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Indicadores financeiros --}}
    
    {{-- Resultado comercial do período --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="?mb-1">Total de vendas</p>
                        <h3 class="mb-1 text-success">
                            {{ $currency($salesTotal) }}
                        </h3>
                        
                        <small class="text-muted d-block">
                            {{ number_format($salesCount, 0, ',', '.') }} venda(s) concluída(s)
                        </small>

                        <small class="d-block mt-1">
                            @if($salesVariation === null)
                                <span class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Sem histórico anterior
                                </span>
                            @else
                                <span class="{{ $salesVariation > 0 ? 'text-success' : ($salesVariation < 0 ? 'text-danger' : 'text-muted') }}">
                                    <i class="bi {{ $salesVariation > 0 ? 'bi-arrow-up' : ($salesVariation < 0 ? 'bi-arrow-down' : 'bi-dash') }}"></i>
                                    {{ number_format(abs($salesVariation), 1, ',', '.') }}%
                                </span>
                                <span class="text-muted">em relação ao período anterior</span>
                            @endif
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Total de compras</p>
                        <h3 class="mb-1 text-danger">
                            {{ $currency($purchasesTotal) }}
                        </h3>                        
                        <small class="text-muted d-block">
                            {{ number_format($purchasesCount, 0, ',', '.') }} compra(s) considerada(s)
                        </small>

                        <small class="d-block mt-1">
                            @if($purchasesVariation === null)
                                <span class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Sem histórico anterior
                                </span>
                            @else
                                <span class="text-muted">
                                    <i class="bi {{ $purchasesVariation > 0 ? 'bi-arrow-up' : ($purchasesVariation < 0 ? 'bi-arrow-down' : 'bi-dash') }}"></i>
                                    {{ number_format(abs($purchasesVariation), 1, ',', '.') }}%
                                </span>
                                <span class="text-muted">em relação ao período anterior</span>
                            @endif
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-bag-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Saldo comercial</p>

                        <h3 class="mb-1 {{ $commercialBalance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $currency($commercialBalance) }}
                        </h3>

                        <small class="text-muted">
                            Vendas menos compras
                        </small>
                    </div>

                    <div class="metric-icon">
                        <i class="bi bi-calculator"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <p class="text-muted mb-1">Contas a receber</p>
                <h3 class="mb-0 text-success">
                    {{ $currencyFromCents($totalReceivableCents) }}
                </h3>
                <small class="text-muted">Saldo em aberto</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <p class="text-muted mb-1">Contas a pagar</p>
                <h3 class="mb-0 text-danger">
                    {{ $currencyFromCents($totalPayableCents) }}
                </h3>
                <small class="text-muted">Saldo em aberto</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-card h-100">
                <p class="text-muted mb-1">Contas vencidas</p>
                <h3 class="mb-0 text-warning">
                    {{ $currencyFromCents($overdueTotalCents) }}
                </h3>
                <small class="text-muted">
                    {{ $overdueCount }} conta(s) em atraso
                </small>
            </div>
        </div>

    </div>

    {{-- Gráfico --}}
    <div class="content-card mb-4">
        <div class="section-heading mb-3">
            <div>
                <h5 class="mb-1">Movimentação comercial</h5>
                <p class="text-muted small mb-0">
                    Comparativo diário entre vendas e compras no período selecionado.
                </p>
            </div>
        </div>

        @if($chartHasData)
            <div class="d-flex flex-wrap gap-3 mb-3">
                <span class="small">
                    <span class="d-inline-block rounded me-1"
                          style="width: 10px; height: 10px; background: #2B6CB0;"></span>
                    Vendas
                </span>

                <span class="small">
                    <span class="d-inline-block rounded me-1"
                          style="width: 10px; height: 10px; background: #38A169;"></span>
                    Compras
                </span>
            </div>

            <div class="d-flex align-items-end gap-2 overflow-auto pb-2"
                 style="min-height: 260px;">

                @foreach($chartLabels as $index => $label)
                    @php
                        $saleValue = (float) ($chartSales[$index] ?? 0);
                        $purchaseValue = (float) ($chartPurchases[$index] ?? 0);

                        $saleHeight = ($saleValue / $maxChartValue) * 190;
                        $purchaseHeight = ($purchaseValue / $maxChartValue) * 190;
                    @endphp

                    <div class="text-center flex-shrink-0"
                         style="min-width: 34px;">

                        <div class="d-flex align-items-end justify-content-center gap-1"
                             style="height: 210px;">

                            <div
                                class="rounded-top"
                                style="
                                    width: 12px;
                                    height: {{ max(2, $saleHeight) }}px;
                                    background: #2B6CB0;
                                    opacity: {{ $saleValue > 0 ? '1' : '0.12' }};
                                "
                                title="Vendas: {{ $currency($saleValue) }}"
                            ></div>

                            <div
                                class="rounded-top"
                                style="
                                    width: 12px;
                                    height: {{ max(2, $purchaseHeight) }}px;
                                    background: #38A169;
                                    opacity: {{ $purchaseValue > 0 ? '1' : '0.12' }};
                                "
                                title="Compras: {{ $currency($purchaseValue) }}"
                            ></div>

                        </div>

                        <small class="text-muted d-block mt-2">
                            {{ $label }}
                        </small>
                    </div>
                @endforeach

            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">
                    Não há movimentações comerciais no período selecionado.
                </p>
            </div>
        @endif
    </div>

    {{-- Produtos mais vendidos e estoque baixo --}}
    <div class="row g-4 mb-4">

        <div class="col-12 col-xl-6">
            <div class="content-card h-100">
                <div class="section-heading mb-3">
                    <div>
                        <h5 class="mb-1">Produtos mais vendidos</h5>
                        <p class="text-muted small mb-0">
                            Top 5 por quantidade vendida no período.
                        </p>
                    </div>

                    <a href="{{ route('sales.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        Ver vendas
                    </a>
                </div>

                @if($bestSellingProducts->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Quantidade</th>
                                    <th class="text-end">Faturamento</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($bestSellingProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>

                                        <td class="text-end">
                                            {{ number_format((float) $product->total_quantity, 3, ',', '.') }}
                                        </td>

                                        <td class="text-end">
                                            {{ $currency($product->total_revenue) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">
                            Nenhuma venda registrada no período.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="content-card h-100">
                <div class="section-heading mb-3">
                    <div>
                        <h5 class="mb-1">Estoque baixo</h5>
                        <p class="text-muted small mb-0">
                            Produtos que atingiram o estoque mínimo.
                        </p>
                    </div>

                    <a href="{{ route('modules.show', 'estoque') }}"
                    class="btn btn-sm btn-outline-primary">
                        Ver estoque
                    </a>
                </div>

                @if($lowStockProducts->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Atual</th>
                                    <th class="text-end">Mínimo</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>

                                        <td class="text-end text-danger fw-semibold">
                                            {{ number_format((float) $product->inventoryStock->quantity, 3, ',', '.') }}
                                            {{ $product->unit?->abbreviation }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format((float) $product->minimum_stock, 3, ',', '.') }}
                                            {{ $product->unit?->abbreviation }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle fs-2 text-success"></i>
                        <p class="text-muted mt-2 mb-0">
                            Nenhum produto com estoque baixo.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Contas vencidas --}}
    <div class="content-card mb-4">
        <div class="section-heading mb-3">
            <div>
                <h5 class="mb-1">Contas vencidas</h5>
                <p class="text-muted small mb-0">
                    Contas em aberto com vencimento anterior à data atual.
                </p>
            </div>

            <a href="{{ route('financial-accounts.index') }}"
               class="btn btn-sm btn-outline-primary">
                Ver financeiro
            </a>
        </div>

        @if($overdueAccounts->isNotEmpty())
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Vencimento</th>
                            <th class="text-end">Saldo pendente</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($overdueAccounts as $account)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $account->description }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $account->customer?->name
                                            ?? $account->supplier?->fantasy_name
                                            ?? $account->supplier?->name
                                            ?? 'Sem vínculo' }}
                                    </small>
                                </td>

                                <td>
                                    @if($account->type === \App\Models\FinancialAccount::TYPE_RECEIVABLE)
                                        <span class="badge bg-success-subtle text-success">
                                            A receber
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">
                                            A pagar
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $account->due_date?->format('d/m/Y') }}
                                </td>

                                <td class="text-end fw-semibold text-danger">
                                    {{ $currencyFromCents($account->pending_amount_cents) }}
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('financial-accounts.show', $account) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-check-circle fs-2 text-success"></i>
                <p class="text-muted mt-2 mb-0">
                    Não existem contas vencidas.
                </p>
            </div>
        @endif
    </div>

    {{-- Últimas movimentações --}}
    <div class="row g-4 mb-4">

        <div class="col-12 col-xl-6">
            <div class="content-card h-100">
                <div class="section-heading mb-3">
                    <div>
                        <h5 class="mb-1">Últimas vendas</h5>
                        <p class="text-muted small mb-0">
                            Vendas concluídas recentemente.
                        </p>
                    </div>

                    <a href="{{ route('sales.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        Ver todas
                    </a>
                </div>

                @if($recentSales->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($recentSales as $sale)
                                    <tr>
                                        <td>{{ $sale->number }}</td>

                                        <td>
                                            {{ $sale->customer?->name ?? 'Consumidor não identificado' }}
                                        </td>

                                        <td>
                                            {{ $sale->sale_date?->format('d/m/Y') }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            {{ $currency($sale->total) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">
                            Nenhuma venda concluída.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="content-card h-100">
                <div class="section-heading mb-3">
                    <div>
                        <h5 class="mb-1">Últimas compras</h5>
                        <p class="text-muted small mb-0">
                            Compras confirmadas ou recebidas recentemente.
                        </p>
                    </div>

                    <a href="{{ route('purchases.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        Ver todas
                    </a>
                </div>

                @if($recentPurchases->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fornecedor</th>
                                    <th>Data</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($recentPurchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->number }}</td>

                                        <td>
                                            {{ $purchase->supplier?->fantasy_name
                                                ?: $purchase->supplier?->name
                                                ?: 'Fornecedor não identificado' }}
                                        </td>

                                        <td>
                                            {{ $purchase->purchase_date?->format('d/m/Y') }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            {{ $currency($purchase->total) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">
                            Nenhuma compra confirmada.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Acesso rápido --}}
    <div class="content-card mb-4">
        <div class="section-heading mb-3">
            <div>
                <h5 class="mb-1">Acesso rápido</h5>
                <p class="text-muted small mb-0">
                    Acesse diretamente os principais módulos do sistema.
                </p>
            </div>
        </div>

        <div class="row g-3">

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'cadastros') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-folder2-open"></i>
                    <h6>Cadastros</h6>
                    <p class="text-muted small mb-0">
                        Gerencie produtos, clientes, fornecedores e categorias.
                    </p>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'estoque') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-boxes"></i>
                    <h6>Estoque</h6>
                    <p class="text-muted small mb-0">
                        Consulte saldos e movimentações de estoque.
                    </p>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'compras') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-bag"></i>
                    <h6>Compras</h6>
                    <p class="text-muted small mb-0">
                        Registre compras e acompanhe recebimentos.
                    </p>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'vendas') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-cart3"></i>
                    <h6>Vendas</h6>
                    <p class="text-muted small mb-0">
                        Registre vendas e acompanhe os pedidos.
                    </p>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'financeiro') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-cash-coin"></i>
                    <h6>Financeiro</h6>
                    <p class="text-muted small mb-0">
                        Acompanhe contas a pagar, receber e transações.
                    </p>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ route('modules.show', 'caixa') }}"
                   class="quick-link-card text-decoration-none d-block h-100">
                    <i class="bi bi-wallet2"></i>
                    <h6>Caixa</h6>
                    <p class="text-muted small mb-0">
                        Consulte o fluxo financeiro e os movimentos de caixa.
                    </p>
                </a>
            </div>

        </div>
    </div>

</div>

{{-- Comportamento dos filtros --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('period');
    const startContainer = document.getElementById('start-date-container');
    const endContainer = document.getElementById('end-date-container');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');

    function updateDateFields() {
        const isCustom = periodSelect.value === 'custom';

        startContainer.classList.toggle('d-none', !isCustom);
        endContainer.classList.toggle('d-none', !isCustom);

        startInput.disabled = !isCustom;
        endInput.disabled = !isCustom;

        startInput.required = isCustom;
        endInput.required = isCustom;
    }

    periodSelect.addEventListener('change', updateDateFields);

    startInput.addEventListener('change', function () {
        endInput.min = startInput.value;
    });

    updateDateFields();
});
</script>
@endsection