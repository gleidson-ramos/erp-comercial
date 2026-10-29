@extends('layouts.app')

@section('content')
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1>Compras</h1>
        <p>Gerencie pedidos de compra e acompanhe o recebimento de produtos.</p>
    </div>

    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        Nova compra
    </a>
</div>

<div class="content-card">
    <form action="{{ route('purchases.index') }}" method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="search" class="form-label">Buscar compra</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Número da compra ou nome do fornecedor"
                >
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todos os status</option>
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

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    Filtrar
                </button>

                <a
                    href="{{ route('purchases.index') }}"
                    class="btn btn-outline-secondary"
                    title="Limpar filtros"
                    aria-label="Limpar filtros"
                >
                    Limpar
                </a>
            </div>
        </div>
    </form>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fornecedor</th>
                    <th>Data da compra</th>
                    <th>Previsão de entrega</th>
                    <th class="text-center">Itens</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($purchases as $purchase)
                    @php
                        $statusClasses = [
                            'RASCUNHO' => 'status-inactive',
                            'CONFIRMADA' => 'status-active',
                            'PARCIALMENTE_RECEBIDA' => 'status-active',
                            'RECEBIDA' => 'status-active',
                            'CANCELADA' => 'status-inactive',
                        ];

                        $statusLabels = [
                            'RASCUNHO' => 'Rascunho',
                            'CONFIRMADA' => 'Confirmada',
                            'PARCIALMENTE_RECEBIDA' => 'Parcialmente recebida',
                            'RECEBIDA' => 'Recebida',
                            'CANCELADA' => 'Cancelada',
                        ];
                    @endphp

                    <tr>
                        <td>
                            <a
                                href="{{ route('purchases.show', $purchase) }}"
                                class="fw-semibold text-decoration-none"
                            >
                                {{ $purchase->number ?: 'Compra #' . $purchase->id }}
                            </a>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $purchase->supplier->fantasy_name ?: $purchase->supplier->name }}
                            </div>
                            @if ($purchase->supplier->fantasy_name)
                                <small class="text-muted">
                                    {{ $purchase->supplier->name }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>

                        <td>
                            {{ $purchase->expected_delivery_date?->format('d/m/Y') ?? '—' }}
                        </td>

                        <td class="text-center">{{ $purchase->items_count }}</td>

                        <td class="text-end text-nowrap">
                            R$ {{ number_format((float) $purchase->total, 2, ',', '.') }}
                        </td>

                        <td>
                            <span class="status-pill {{ $statusClasses[$purchase->status] ?? 'status-inactive' }}">
                                {{ $statusLabels[$purchase->status] ?? $purchase->status }}
                            </span>
                        </td>

                        <td class="text-end text-nowrap">
                            <a
                                href="{{ route('purchases.show', $purchase) }}"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Visualizar
                            </a>

                            @if ($purchase->status === \App\Models\Purchase::STATUS_DRAFT)
                                <a
                                    href="{{ route('purchases.edit', $purchase) }}"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Editar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="fw-semibold">Nenhuma compra encontrada</div>
                            <p class="text-muted mb-3">
                                Cadastre uma compra ou altere os filtros da pesquisa.
                            </p>

                            <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                                Nova compra
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
        <small class="text-muted">
            Exibindo {{ $purchases->firstItem() ?? 0 }}–{{ $purchases->lastItem() ?? 0 }}
            de {{ $purchases->total() }} compras
        </small>

        {{ $purchases->links() }}
    </div>
</div>
@endsection