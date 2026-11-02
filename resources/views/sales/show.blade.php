
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
        <div class="col-md-6">
            <div class="text-muted small">Cliente</div>
            <div class="fw-semibold">
                {{ $sale->customer?->name ?? 'Consumidor não identificado' }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Data da venda</div>
            <div class="fw-semibold">
                {{ $sale->sale_date->format('d/m/Y') }}
            </div>
        </div>
        
        <div class="col-md-2">
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

        <div class="row g-3 mt-2">
            {{-- CRIAÇÃO --}}
            <div class="col-md-4">
                    <div class="text-muted small mb-1">
                        Criada por
                    </div>

                    <strong>
                        {{ $creator?->user_name ?? 'Não identificado' }}
                    </strong>

                    @if ($creator)
                        <div class="text-muted small mt-1">
                            {{ $creator->created_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
            </div>

            {{-- CONCLUSÃO --}}
            <div class="col-md-4">
                    <div class="text-muted small mb-1">
                        Concluída por
                    </div>

                    @if ($completionLog)
                        <strong>{{ $completionLog->user_name ?? 'Não identificado' }}</strong>

                        <div class="text-muted small mt-1">
                            {{ $completionLog->created_at->format('d/m/Y H:i') }}
                        </div>
                    @else
                        <span class="text-muted">Ainda não concluída</span>
                    @endif
            </div>

            {{-- CANCELAMENTO --}}
                <div class="col-md-4">
                    @if ($cancellationLog)
                    <div class="text-muted small mb-1">
                        Cancelada por
                    </div>
                        <strong>{{ $cancellationLog->user_name ?? 'Não identificado' }}</strong>

                        <div class="text-muted small mt-1">
                            {{ $cancellationLog->created_at->format('d/m/Y H:i') }}
                        </div>
                    @else
                        <span class="text-muted"></span>
                    @endif
                </div>
        </div>
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

{{-- DOCUMENTOS DA VENDA --}}
<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="mb-1">Documentos da venda</h5>
            <p class="text-muted mb-0">
                Pedidos, autorizações, comprovantes de entrega e outros documentos comerciais.
            </p>
        </div>

        <span class="badge bg-secondary">
            {{ $sale->attachments->count() }} arquivo(s)
        </span>
    </div>

    @if ($sale->attachments->isNotEmpty())
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Arquivo</th>
                        <th>Enviado por</th>
                        <th>Data do envio</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sale->attachments->sortByDesc('created_at') as $attachment)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $attachment->original_name }}
                                </div>

                                @if ($attachment->description)
                                    <div class="text-muted small">
                                        {{ $attachment->description }}
                                    </div>
                                @endif

                                <div class="text-muted small">
                                    {{ number_format($attachment->file_size / 1024, 2, ',', '.') }} KB
                                </div>
                            </td>

                            <td>
                                {{ $attachment->uploader?->name ?? 'Usuário não identificado' }}
                            </td>

                            <td>
                                {{ $attachment->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a
                                        href="{{ route('attachments.preview', $attachment) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Visualizar documento"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Visualizar
                                    </a>

                                    <a
                                        href="{{ route('attachments.download', $attachment) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Baixar documento"
                                    >
                                        <i class="bi bi-download"></i>
                                        Baixar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-paperclip fs-3 d-block mb-2"></i>
            Nenhum documento foi anexado a esta venda.
        </div>
    @endif
</div>

{{-- CONTA A RECEBER --}}
@if ($sale->financialAccount)
    @php
        $account = $sale->financialAccount;

        $paidAmount = (float) $account->transactions->sum('amount');

        $totalAmount = (float) $account->amount
            - (float) $account->discount
            + (float) $account->interest;

        $balance = max(0, $totalAmount - $paidAmount);
    @endphp

    <div class="content-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1">Conta a receber</h5>
                <p class="text-muted mb-0">
                    Conta financeira vinculada a esta venda.
                </p>
            </div>

            <span class="status-pill
                {{ $account->status === \App\Models\FinancialAccount::STATUS_PAID
                    ? 'status-active'
                    : 'status-inactive' }}">
                {{ str_replace('_', ' ', $account->status) }}
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small">Documento</div>
                <div class="fw-semibold">
                    {{ $account->document_number ?: 'Não informado' }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-muted small">Emissão</div>
                <div class="fw-semibold">
                    {{ $account->issue_date?->format('d/m/Y') ?? '—' }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-muted small">Vencimento</div>
                <div class="fw-semibold">
                    {{ $account->due_date?->format('d/m/Y') ?? '—' }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-muted small">Valor original</div>
                <div class="fw-semibold">
                    R$ {{ number_format((float) $account->amount, 2, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Total com ajustes</div>
                <div class="fw-semibold">
                    R$ {{ number_format($totalAmount, 2, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Recebido</div>
                <div class="fw-semibold text-success">
                    R$ {{ number_format($paidAmount, 2, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Saldo restante</div>
                <div class="fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                    R$ {{ number_format($balance, 2, ',', '.') }}
                </div>
            </div>
        </div>

        @if ($account->status === \App\Models\FinancialAccount::STATUS_CANCELLED)
            <div class="alert alert-warning mt-3 mb-0">
                Esta conta a receber foi cancelada.
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('financial-accounts.show', $account) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cash-stack"></i>
                Ver conta financeira
            </a>
        </div>
    </div>
@endif


{{-- VENCIMENTO DA CONTA A RECEBER --}}
@if ($sale->isDraft())
    <div class="content-card mb-4">
        <h5 class="mb-2">Vencimento da conta a receber</h5>
        <p class="text-muted mb-3">
            Defina a data de vencimento que será utilizada na geração da conta
            a receber ao concluir esta venda.
        </p>

        <form action="{{ route('sales.complete', $sale) }}" method="POST"
              id="complete-sale-form">
            @csrf

            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="due_date" class="form-label">Data de vencimento</label>
                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        class="form-control"
                        value="{{ old('due_date', $sale->sale_date->format('Y-m-d')) }}"
                        min="{{ $sale->sale_date->format('Y-m-d') }}"
                        required
                    >

                    @error('due_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>
    </div>
@endif

{{-- AÇÕES DA VENDA --}}
@if ($sale->isDraft())
    <div class="content-card mb-4">
        <h5 class="mb-2">Ações da venda</h5>
        <p class="text-muted mb-3">
            Esta venda ainda está em rascunho. O estoque não foi movimentado.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sales.edit', $sale) }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar venda
            </a>

            <button
                type="submit"
                form="complete-sale-form"
                class="btn btn-primary"
                onclick="return confirm('Deseja concluir esta venda, baixar os produtos do estoque e gerar a conta a receber?');"
            >
                <i class="bi bi-check-circle"></i> Concluir venda
            </button>

            <form action="{{ route('sales.cancel', $sale) }}"
                  method="POST"
                  onsubmit="return confirm('Deseja cancelar este rascunho?');">
                @csrf

                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Cancelar rascunho
                </button>
            </form>
        </div>
    </div>

@elseif ($sale->status === \App\Models\Sale::STATUS_COMPLETED)
    <div class="content-card mb-4">
        <h5 class="mb-2">Ações da venda</h5>
        <p class="text-muted mb-3">
            Esta venda foi concluída e os produtos já foram baixados do estoque.
            Ao cancelar, as quantidades vendidas serão devolvidas ao estoque.
        </p>

        <form action="{{ route('sales.cancel', $sale) }}"
              method="POST"
              onsubmit="return confirm('ATENÇÃO: deseja cancelar esta venda e devolver todos os produtos ao estoque?');">
            @csrf

            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-x-circle"></i> Cancelar venda e estornar estoque
            </button>
        </form>
    </div>
@endif
@endsection