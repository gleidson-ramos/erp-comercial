@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Vendas</h1>
        <p>Gerencie as vendas e acompanhe seus respectivos status.</p>
    </div>

    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
        Nova venda
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('sales.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Número da venda ou cliente"
                >
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($statuses as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(request('status') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">Data inicial</label>
                <input
                    type="date"
                    name="date_from"
                    id="date_from"
                    class="form-control"
                    value="{{ request('date_from') }}"
                >
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Data final</label>
                <input
                    type="date"
                    name="date_to"
                    id="date_to"
                    class="form-control"
                    value="{{ request('date_to') }}"
                >
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Filtrar
                </button>

                <a href="{{ route('sales.index') }}"
                   class="btn btn-outline-secondary">
                    Limpar
                </a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>
                            {{ $sale->number ?: 'Venda #' . $sale->id }}
                        </td>

                        <td>
                            {{ $sale->customer?->name ?? 'Consumidor não identificado' }}
                        </td>

                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>

                        <td>{{ $sale->items_count }}</td>

                        <td>
                            R$ {{ number_format((float) $sale->total, 2, ',', '.') }}
                        </td>

                        <td>
                            @if ($sale->status === \App\Models\Sale::STATUS_DRAFT)
                                <span class="status-pill status-inactive">
                                    Rascunho
                                </span>
                            @elseif ($sale->status === \App\Models\Sale::STATUS_COMPLETED)
                                <span class="status-pill status-active">
                                    Concluída
                                </span>
                            @else
                                <span class="status-pill status-inactive">
                                    Cancelada
                                </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Detalhes
                            </a>

                            @if ($sale->isDraft())
                                <a
                                    href="{{ route('sales.edit', $sale) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Editar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            Nenhuma venda encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $sales->links() }}
    </div>
</div>
@endsection