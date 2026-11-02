
@extends('layouts.app')

@section('title', 'Relatório de Estoque')
@section('page-heading', 'Relatório de Estoque')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Relatório de Estoque</h4>
            <p class="text-muted mb-0">
                Consulte a posição atual e identifique produtos que precisam de reposição.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a href="{{ route('reports.stock.pdf', request()->query()) }}"
            class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF
            </a>

            <a href="{{ route('reports.stock.export', request()->query()) }}"
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

    {{-- Filtro --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.stock.index') }}">
                
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="date_from" class="form-label">Data inicial</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        class="form-control"
                        value="{{ $filters['date_from'] }}"
                    >
                </div>

                <div class="col-md-3">
                    <label for="date_to" class="form-label">Data final</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        class="form-control"
                        value="{{ $filters['date_to'] }}"
                    >
                </div>

                <div class="col-md-3">
                    <label for="product_id" class="form-label">Produto</label>

                    <select name="product_id" id="product_id" class="form-select">
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
                </div>

                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo de movimentação</label>

                    <select name="type" id="type" class="form-select">
                        <option value="">Entradas e saídas</option>

                        <option value="IN" @selected($filters['type'] === 'IN')>
                            Entradas
                        </option>

                        <option value="OUT" @selected($filters['type'] === 'OUT')>
                            Saídas
                        </option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a
                        href="{{ route('reports.stock.index') }}"
                        class="btn btn-outline-secondary"
                    >
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

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Produtos com registro de estoque</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($summary['products_count'], 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="text-primary fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Quantidade total em estoque</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($summary['total_quantity'], 3, ',', '.') }}
                        </h3>
                    </div>
                    <div class="text-success fs-3">
                        <i class="bi bi-archive"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Produtos no estoque mínimo</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($summary['low_stock_count'], 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="text-warning fs-3">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    {{-- Indicadores de movimentação --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Movimentações no período</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($movementSummary['total_movements'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="text-primary fs-3">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Quantidade de entradas</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($movementSummary['total_entries'], 3, ',', '.') }}
                        </h3>
                    </div>

                    <div class="text-success fs-3">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Quantidade de saídas</p>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($movementSummary['total_exits'], 3, ',', '.') }}
                        </h3>
                    </div>

                    <div class="text-danger fs-3">
                        <i class="bi bi-box-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Posição atual --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-boxes me-2"></i>
                Posição atual do estoque
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Unidade</th>
                            <th class="text-end">Quantidade atual</th>
                            <th class="text-end">Estoque mínimo</th>
                            <th class="text-center">Situação</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($stockPosition as $stock)

                            @php
                                $minimum = (float) ($stock->product?->minimum_stock ?? 0);
                                $quantity = (float) $stock->quantity;
                                $belowMinimum = $quantity <= $minimum;
                            @endphp

                            <tr>
                                <td class="fw-semibold">
                                    {{ $stock->product?->name ?? 'Produto não encontrado' }}
                                </td>

                                <td class="text-center">
                                    {{ $stock->product?->unit?->abbreviation
                                        ?? $stock->product?->unit?->name
                                        ?? '-' }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($minimum, 3, ',', '.') }}
                                </td>

                                <td class="text-center">
                                    @if($belowMinimum)
                                        <span class="badge text-bg-warning">
                                            Estoque mínimo
                                        </span>
                                    @else
                                        <span class="badge text-bg-success">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhum registro de estoque encontrado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="p-3">
                {{ $stockPosition->links() }}
            </div>

        </div>
    </div>

    {{-- Produtos abaixo do mínimo --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                Produtos que precisam de reposição
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th class="text-end">Estoque atual</th>
                            <th class="text-end">Estoque mínimo</th>
                            <th class="text-end">Quantidade para atingir o mínimo</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($lowStockProducts as $stock)

                            @php
                                $minimum = (float) $stock->product->minimum_stock;
                                $quantity = (float) $stock->quantity;
                                $replenishment = max(0, $minimum - $quantity);
                            @endphp

                            <tr>
                                <td class="fw-semibold">
                                    {{ $stock->product->name }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($minimum, 3, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold text-danger">
                                    {{ number_format($replenishment, 3, ',', '.') }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum produto no estoque mínimo.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="p-3">
                {{ $lowStockProducts->links() }}
            </div>

        </div>
    </div>

    
    {{-- Histórico de movimentações --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-arrow-left-right me-2"></i>
                Histórico de movimentações
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-end">Quantidade</th>
                            <th class="text-end">Custo unitário</th>
                            <th class="text-end">Saldo após movimento</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($movements as $movement)

                            <tr>
                                <td>
                                    {{ $movement->moved_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $movement->product?->name ?? 'Produto não encontrado' }}
                                </td>

                                <td class="text-center">
                                    @if($movement->type === 'IN')
                                        <span class="badge text-bg-success">
                                            Entrada
                                        </span>
                                    @elseif($movement->type === 'OUT')
                                        <span class="badge text-bg-danger">
                                            Saída
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary">
                                            {{ $movement->type }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    {{ number_format($movement->quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    R$ {{ number_format($movement->unit_cost ?? 0, 2, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($movement->balance_after, 3, ',', '.') }}
                                </td>

                                <td>
                                    {{ $movement->reason ?: '-' }}

                                    @if($movement->notes)
                                        <i
                                            class="bi bi-info-circle text-muted ms-1"
                                            title="{{ $movement->notes }}"
                                        ></i>
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Nenhuma movimentação encontrada no período.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="p-3">
                {{ $movements->links() }}
            </div>

        </div>
    </div>

</div>

@endsection