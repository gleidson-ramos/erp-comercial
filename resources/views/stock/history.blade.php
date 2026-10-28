@extends('layouts.app')

@section('title', 'Histórico de estoque')

@section('content')
<div class="page-heading">
    <div>
        <h1>Histórico de movimentações</h1>
        <p class="text-muted mb-0">
            Consulte as entradas e saídas registradas.
        </p>
    </div>

    <a href="{{ route('stock.create') }}" class="btn btn-primary">
        Nova movimentação
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('stock.history') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Produto</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Nome ou SKU"
                >
            </div>

            <div class="col-md-2">
                <label for="type" class="form-label">Tipo</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Todos</option>
                    <option value="IN" @selected(request('type') === 'IN')>
                        Entrada
                    </option>
                    <option value="OUT" @selected(request('type') === 'OUT')>
                        Saída
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">De</label>
                <input
                    type="date"
                    name="date_from"
                    id="date_from"
                    class="form-control"
                    value="{{ request('date_from') }}"
                >
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Até</label>
                <input
                    type="date"
                    name="date_to"
                    id="date_to"
                    class="form-control"
                    value="{{ request('date_to') }}"
                >
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('stock.history') }}"
                   class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Custo unitário</th>
                    <th>Motivo</th>
                    <th>Saldo após</th>
                </tr>
            </thead>

            <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>
                            {{ $movement->moved_at->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $movement->product->name }}
                            </div>
                            <small class="text-muted">
                                SKU: {{ $movement->product->sku }}
                            </small>
                        </td>

                        <td>
                            @if($movement->type === 'IN')
                                <span class="status-pill status-active">Entrada</span>
                            @else
                                <span class="status-pill status-inactive">Saída</span>
                            @endif
                        </td>

                        <td>
                            {{ number_format((float) $movement->quantity, 3, ',', '.') }}
                            {{ $movement->product->unit?->abbreviation }}
                        </td>

                        <td>
                            @if($movement->unit_cost !== null)
                                R$ {{ number_format((float) $movement->unit_cost, 2, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            {{ [
                                'COMPRA' => 'Compra',
                                'DEVOLUCAO' => 'Devolução',
                                'AJUSTE' => 'Ajuste',
                                'PERDA' => 'Perda',
                                'USO_INTERNO' => 'Uso interno',
                                'OUTRO' => 'Outro',
                            ][$movement->reason] ?? $movement->reason }}
                        </td>

                        <td class="fw-semibold">
                            {{ number_format((float) $movement->balance_after, 3, ',', '.') }}
                        </td>
                    </tr>

                    @if($movement->notes)
                        <tr>
                            <td colspan="7" class="small text-muted pt-0">
                                Observações: {{ $movement->notes }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Nenhuma movimentação encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $movements->links() }}
    </div>
</div>
@endsection