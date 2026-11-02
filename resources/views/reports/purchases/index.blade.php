
@extends('layouts.app')

@section('title', 'Relatório de Compras')
@section('page-heading', 'Relatório de Compras')

@section('content')

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Relatório de Compras</h4>
            <p class="text-muted mb-0">
                Acompanhe as compras, fornecedores e produtos adquiridos.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a href="{{ route('reports.purchases.pdf', request()->query()) }}"
            class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF
            </a>

            <a href="{{ route('reports.purchases.export', request()->query()) }}"
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

            <form method="GET" action="{{ route('reports.purchases.index') }}">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Data inicial</label>
                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            class="form-control @error('date_from') is-invalid @enderror"
                            value="{{ $filters['date_from'] }}"
                        >
                        @error('date_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Data final</label>
                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            class="form-control @error('date_to') is-invalid @enderror"
                            value="{{ $filters['date_to'] }}"
                        >
                        @error('date_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="supplier_id" class="form-label">Fornecedor</label>
                        <select
                            name="supplier_id"
                            id="supplier_id"
                            class="form-select @error('supplier_id') is-invalid @enderror"
                        >
                            <option value="">Todos os fornecedores</option>

                            @foreach($suppliers as $supplier)
                                <option
                                    value="{{ $supplier->id }}"
                                    @selected((string) $filters['supplier_id'] === (string) $supplier->id)
                                >
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="product_id" class="form-label">Produto</label>
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

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a
                            href="{{ route('reports.purchases.index') }}"
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
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Compras realizadas</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($summary['total_purchases'], 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="text-primary fs-3">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Valor das compras</p>
                        <h3 class="fw-bold mb-0">
                            R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="text-success fs-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Descontos recebidos</p>
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

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
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

    {{-- Evolução diária --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-bar-chart-line me-2"></i>
                Evolução diária das compras
            </h5>
        </div>

        <div class="card-body">

            @forelse($dailyPurchases as $day)

                @php
                    $percentage = $maxDailyAmount > 0
                        ? ($day->total_amount / $maxDailyAmount) * 100
                        : 0;
                @endphp

                <div class="mb-3">

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">
                            {{ \Carbon\Carbon::parse($day->purchase_date)->format('d/m/Y') }}
                        </span>

                        <span class="fw-semibold">
                            R$ {{ number_format($day->total_amount, 2, ',', '.') }}
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
                        {{ $day->purchases_count }} compra(s)
                    </small>

                </div>

            @empty

                <p class="text-muted text-center py-3 mb-0">
                    Nenhuma compra encontrada no período.
                </p>

            @endforelse

        </div>
    </div>

    {{-- Compras por fornecedor --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-truck me-2"></i>
                Compras por fornecedor
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Fornecedor</th>
                            <th class="text-center">Quantidade de compras</th>
                            <th class="text-end">Valor total</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($suppliersReport as $row)

                            <tr>
                                <td>
                                    {{ $row->supplier?->name ?? 'Fornecedor não informado' }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($row->purchases_count, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($row->total_amount, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhuma compra encontrada.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Produtos mais comprados --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-box-seam me-2"></i>
                Produtos mais comprados
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Quantidade comprada</th>
                            <th class="text-center">Compras</th>
                            <th class="text-end">Valor total</th>
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
                                    {{ number_format($row->purchases_count, 0, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($row->total_amount, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Histórico de compras --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-receipt me-2"></i>
                Histórico de compras
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Fornecedor</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($purchases as $purchase)

                            <tr>
                                <td>{{ $purchase->number }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $purchase->supplier?->name ?? 'Fornecedor não informado' }}
                                </td>

                                <td>
                                    @php
                                        $statusLabels = [
                                            'CONFIRMADA' => 'Confirmada',
                                            'PARCIALMENTE_RECEBIDA' => 'Parcialmente recebida',
                                            'RECEBIDA' => 'Recebida',
                                            'CONFIRMED' => 'Confirmada',
                                            'PARTIAL' => 'Parcialmente recebida',
                                            'RECEIVED' => 'Recebida',
                                        ];

                                        $statusColors = [
                                            'CONFIRMADA' => 'primary',
                                            'PARCIALMENTE_RECEBIDA' => 'warning',
                                            'RECEBIDA' => 'success',
                                            'CONFIRMED' => 'primary',
                                            'PARTIAL' => 'warning',
                                            'RECEIVED' => 'success',
                                        ];
                                    @endphp

                                    <span class="badge text-bg-{{ $statusColors[$purchase->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$purchase->status] ?? $purchase->status }}
                                    </span>
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($purchase->total, 2, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhuma compra encontrada.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="p-3">
                {{ $purchases->links() }}
            </div>

        </div>
    </div>

</div>

@endsection