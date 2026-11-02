
@extends('layouts.app')

@section('title', 'Relatório de Vendas')
@section('page-heading', 'Relatório de Vendas')

@section('content')

<div class="container-fluid px-0">
    {{-- Cabeçalho --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Relatório de Vendas</h4>
            <p class="text-muted mb-0">
                Acompanhe o faturamento e o desempenho das vendas.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a href="{{ route('reports.sales.pdf', request()->query()) }}"
            class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF
            </a>

            <a href="{{ route('reports.sales.export', request()->query()) }}"
            class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>
                Exportar Excel
            </a>

            <a href="{{ route('reports.index') }}"
            class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>


    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('reports.sales.index') }}">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">
                            Data inicial
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            class="form-control @error('date_from') is-invalid @enderror"
                            value="{{ old('date_from', $filters['date_from']) }}"
                        >

                        @error('date_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">
                            Data final
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            class="form-control @error('date_to') is-invalid @enderror"
                            value="{{ old('date_to', $filters['date_to']) }}"
                        >

                        @error('date_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">
                            Cliente
                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                            class="form-select @error('customer_id') is-invalid @enderror"
                        >
                            <option value="">Todos os clientes</option>

                            @foreach($customers as $customer)
                                <option
                                    value="{{ $customer->id }}"
                                    @selected((string) $filters['customer_id'] === (string) $customer->id)
                                >
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="product_id" class="form-label">
                            Produto
                        </label>

                        <select
                            name="product_id"
                            id="product_id"
                            class="form-select @error('product_id') is-invalid @enderror"
                        >
                            <option value="">Todos os produtos</option>

                            @foreach($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    @selected((string) $filters['product_id'] === (string) $product->id)
                                >
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        <a
                            href="{{ route('reports.sales.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-x-circle me-1"></i>
                            Limpar filtros
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            Aplicar filtros
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Vendas concluídas</p>
                            <h3 class="fw-bold mb-0">
                                {{ number_format($summary['total_sales'], 0, ',', '.') }}
                            </h3>
                        </div>

                        <div class="text-primary fs-3">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Faturamento</p>
                            <h3 class="fw-bold mb-0">
                                R$ {{ number_format($summary['total_revenue'], 2, ',', '.') }}
                            </h3>
                        </div>

                        <div class="text-success fs-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Descontos concedidos</p>
                            <h3 class="fw-bold mb-0">
                                R$ {{ number_format($summary['total_discount'], 2, ',', '.') }}
                            </h3>
                        </div>

                        <div class="text-warning fs-3">
                            <i class="bi bi-tag"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">Ticket médio</p>
                            <h3 class="fw-bold mb-0">
                                R$ {{ number_format($summary['average_ticket'], 2, ',', '.') }}
                            </h3>
                        </div>

                        <div class="text-info fs-3">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Faturamento diário --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-bar-chart-line me-2"></i>
                Faturamento diário
            </h5>
        </div>

        <div class="card-body">

            @forelse($dailyRevenue as $day)

                @php
                    $percentage = $maxDailyRevenue > 0
                        ? ($day->total_revenue / $maxDailyRevenue) * 100
                        : 0;
                @endphp

                <div class="mb-3">

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">
                            {{ \Carbon\Carbon::parse($day->sale_date)->format('d/m/Y') }}
                        </span>

                        <span class="fw-semibold">
                            R$ {{ number_format($day->total_revenue, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="progress" style="height: 9px;">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            style="width: {{ $percentage }}%"
                            aria-valuenow="{{ $percentage }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>

                    <small class="text-muted">
                        {{ $day->sales_count }} venda(s)
                    </small>

                </div>

            @empty

                <p class="text-muted text-center mb-0 py-3">
                    Nenhuma venda encontrada no período.
                </p>

            @endforelse

        </div>
    </div>

    {{-- Vendas por cliente --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-people me-2"></i>
                Vendas por cliente
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th class="text-center">Quantidade de vendas</th>
                            <th class="text-end">Faturamento</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($customersReport as $row)

                            <tr>
                                <td>
                                    {{ $row->customer?->name ?? 'Cliente não informado' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($row->sales_count, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($row->total_revenue, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhuma venda encontrada.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Produtos mais vendidos --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-box-seam me-2"></i>
                Produtos mais vendidos
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Quantidade vendida</th>
                            <th class="text-center">Vendas</th>
                            <th class="text-end">Valor vendido</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($productsReport as $row)

                            <tr>
                                <td>
                                    {{ $row->product?->name ?? 'Produto não encontrado' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($row->total_quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($row->sales_count, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($row->total_revenue, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum produto vendido no período.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Histórico de vendas --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-receipt me-2"></i>
                Vendas do período
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($sales as $sale)

                            <tr>
                                <td>
                                    {{ $sale->number }}
                                </td>

                                <td>
                                    {{ $sale->sale_date?->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $sale->customer?->name ?? 'Cliente não informado' }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($sale->total, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhuma venda encontrada.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="p-3">
                {{ $sales->links() }}
            </div>

        </div>
    </div>

</div>

@endsection