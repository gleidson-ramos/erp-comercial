@extends('layouts.app')

@section('content')
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1>Receber compra</h1>
        <p>
            Compra {{ $purchase->number ?: '#' . $purchase->id }}
            — {{ $purchase->supplier->fantasy_name ?: $purchase->supplier->name }}
        </p>
    </div>

    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary">
        Voltar à compra
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">
            Não foi possível registrar o recebimento:
        </div>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card mb-4">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="text-muted small">Data da compra</div>
            <div class="fw-semibold">{{ $purchase->purchase_date->format('d/m/Y') }}</div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Valor total</div>
            <div class="fw-semibold">
                R$ {{ number_format((float) $purchase->total, 2, ',', '.') }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Status atual</div>
            <div class="fw-semibold">
                {{ $purchase->status === 'PARCIALMENTE_RECEBIDA' ? 'Parcialmente recebida' : 'Confirmada' }}
            </div>
        </div>
    </div>
</div>

<form action="{{ route('purchases.receipts.store', $purchase) }}" method="POST">
    @csrf

    <div class="content-card">
        <div class="mb-3">
            <h2 class="h5 mb-1">Produtos a receber</h2>
            <p class="text-muted mb-0">
                Preencha apenas as quantidades efetivamente entregues.
                Deixe em branco os produtos que não foram recebidos.
            </p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>SKU</th>
                        <th class="text-end">Quantidade comprada</th>
                        <th class="text-end">Já recebida</th>
                        <th class="text-end">Pendente</th>
                        <th style="min-width: 180px;">Quantidade desta entrega</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($purchase->items as $item)
                        @php
                            $pending = $item->pendingQuantity();
                        @endphp

                        @if ($pending > 0)
                            <tr>
                                <td class="fw-semibold">{{ $item->product->name }}</td>
                                <td>{{ $item->product->sku }}</td>

                                <td class="text-end">
                                    {{ number_format((float) $item->quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-end">
                                    {{ number_format((float) $item->received_quantity, 3, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($pending, 3, ',', '.') }}
                                </td>

                                <td>
                                    <input
                                        type="hidden"
                                        name="items[{{ $loop->index }}][purchase_item_id]"
                                        value="{{ $item->id }}"
                                    >

                                    <input
                                        type="number"
                                        name="items[{{ $loop->index }}][quantity]"
                                        class="form-control receipt-quantity"
                                        min="0.001"
                                        max="{{ number_format($pending, 3, '.', '') }}"
                                        step="0.001"
                                        value="{{ old('items.' . $loop->index . '.quantity') }}"
                                        placeholder="0,000"
                                        aria-label="Quantidade recebida de {{ $item->product->name }}"
                                    >
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label for="received_at" class="form-label">Data e hora do recebimento *</label>
                <input
                    type="datetime-local"
                    name="received_at"
                    id="received_at"
                    class="form-control"
                    value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}"
                    required
                >
            </div>

            <div class="col-md-8">
                <label for="notes" class="form-label">Observações</label>
                <textarea
                    name="notes"
                    id="notes"
                    class="form-control"
                    rows="2"
                    maxlength="2000"
                    placeholder="Ex.: entrega parcial, itens pendentes ou divergências..."
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="form-actions mt-4">
            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Registrar recebimento
            </button>
        </div>
    </div>
</form>
@endsection