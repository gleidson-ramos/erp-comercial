@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>
            {{ $sale->number ?: 'Venda #' . $sale->id }}
        </h1>
        <p>Detalhes, produtos e valores da venda.</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('sales.index') }}"
           class="btn btn-outline-secondary">
            Voltar
        </a>

        @if ($sale->isDraft())
            <a href="{{ route('sales.edit', $sale) }}"
               class="btn btn-outline-primary">
                Editar venda
            </a>
        @endif
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="content-card mb-4">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="text-muted small">Status</div>

            <div class="mt-1">
                @if ($sale->status === \App\Models\Sale::STATUS_DRAFT)
                    <span class="status-pill status-inactive">Rascunho</span>
                @elseif ($sale->status === \App\Models\Sale::STATUS_COMPLETED)
                    <span class="status-pill status-active">Concluída</span>
                @else
                    <span class="status-pill status-inactive">Cancelada</span>
                @endif
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Cliente</div>
            <div class="fw-semibold">
                {{ $sale->customer?->name ?? 'Consumidor não identificado' }}
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Data da venda</div>
            <div class="fw-semibold">
                {{ $sale->sale_date->format('d/m/Y') }}
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Criada em</div>
            <div class="fw-semibold">
                {{ $sale->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        @if ($sale->completed_at)
            <div class="col-md-3">
                <div class="text-muted small">Concluída em</div>
                <div class="fw-semibold">
                    {{ $sale->completed_at->format('d/m/Y H:i') }}
                </div>
            </div>
        @endif

        @if ($sale->cancelled_at)
            <div class="col-md-3">
                <div class="text-muted small">Cancelada em</div>
                <div class="fw-semibold">
                    {{ $sale->cancelled_at->format('d/m/Y H:i') }}
                </div>
            </div>
        @endif
    </div>

    @if ($sale->notes)
        <hr>

        <div class="text-muted small">Observações</div>
        <p class="mb-0">{{ $sale->notes }}</p>
    @endif
</div>

<div class="content-card mb-4">
    <h5 class="mb-3">Produtos da venda</h5>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço unitário</th>
                    <th>Desconto</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($sale->items as $item)
                    <tr>
                        <td>
                            {{ $item->product?->name ?? 'Produto indisponível' }}
                        </td>

                        <td>
                            {{ number_format((float) $item->quantity, 3, ',', '.') }}
                        </td>

                        <td>
                            R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}
                        </td>

                        <td>
                            R$ {{ number_format((float) $item->discount, 2, ',', '.') }}
                        </td>

                        <td class="text-end">
                            R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            Nenhum produto registrado nesta venda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal dos itens</span>
                <span>
                    R$ {{ number_format((float) $sale->subtotal, 2, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Desconto geral</span>
                <span>
                    − R$ {{ number_format((float) $sale->discount, 2, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between border-top pt-3">
                <strong>Total</strong>
                <strong class="fs-5">
                    R$ {{ number_format((float) $sale->total, 2, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>
</div>

@if ($sale->isDraft())
    <div class="content-card">
        <h5>Ações da venda</h5>

        <p class="text-muted">
            Esta venda ainda é um rascunho. O estoque não foi movimentado.
        </p>

        
        <form
            action="{{ route('sales.complete', $sale) }}"
            method="POST"
            class="mb-3"
            onsubmit="return confirm('Deseja concluir esta venda e baixar os produtos do estoque?');"
        >
            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i>
                Concluir venda
            </button>
        </form>


        <form
            action="{{ route('sales.cancel', $sale) }}"
            method="POST"
            onsubmit="return confirm('Deseja cancelar este rascunho de venda?');"
        >
            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-outline-danger">
                Cancelar rascunho
            </button>
        </form>
        
    </div>
@endif
@endsection