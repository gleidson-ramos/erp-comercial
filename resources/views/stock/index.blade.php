@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
<div class="page-heading">
    <div>
        <h1>Estoque</h1>
        <p class="text-muted mb-0">
            Consulte os saldos e identifique produtos com estoque baixo.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('stock.history') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>
            Histórico
        </a>

        <a href="{{ route('stock.create') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left-right me-1"></i>
            Nova movimentação
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('stock.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="search" class="form-label">Pesquisar produto</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Nome, SKU ou código de barras"
                >
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Situação do estoque</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="low" @selected(request('status') === 'low')>
                        Estoque baixo
                    </option>
                    <option value="available" @selected(request('status') === 'available')>
                        Acima do mínimo
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    Filtrar
                </button>
                <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                    Limpar
                </a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Saldo atual</th>
                    <th>Estoque mínimo</th>
                    <th>Situação</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)
                    @php
                        $quantity = (float) ($product->inventoryStock?->quantity ?? 0);
                        $minimum = (float) $product->minimum_stock;
                        $lowStock = $quantity <= $minimum;
                    @endphp

                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <small class="text-muted">
                                SKU: {{ $product->sku }}
                                @if($product->unit)
                                    · Unidade: {{ $product->unit->abbreviation }}
                                @endif
                            </small>
                        </td>

                        <td>{{ $product->category?->name ?? '—' }}</td>

                        <td class="fw-semibold">
                            {{ number_format($quantity, 3, ',', '.') }}
                        </td>

                        <td>
                            {{ number_format($minimum, 3, ',', '.') }}
                        </td>

                        <td>
                            @if($lowStock)
                                <span class="status-pill status-inactive">
                                    Estoque baixo
                                </span>
                            @else
                                <span class="status-pill status-active">
                                    Disponível
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $products->links() }}
    </div>
</div>
@endsection