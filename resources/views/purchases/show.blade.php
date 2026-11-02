@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        'RASCUNHO' => 'Rascunho',
        'CONFIRMADA' => 'Confirmada',
        'PARCIALMENTE_RECEBIDA' => 'Parcialmente recebida',
        'RECEBIDA' => 'Recebida',
        'CANCELADA' => 'Cancelada',
    ];

    $statusClasses = [
        'RASCUNHO' => 'status-inactive',
        'CONFIRMADA' => 'status-active',
        'PARCIALMENTE_RECEBIDA' => 'status-active',
        'RECEBIDA' => 'status-active',
        'CANCELADA' => 'status-inactive',
    ];

    $canEdit = $purchase->status === \App\Models\Purchase::STATUS_DRAFT;

    $canCancel = in_array($purchase->status, [
        \App\Models\Purchase::STATUS_DRAFT,
        \App\Models\Purchase::STATUS_CONFIRMED,
    ], true) && ! $purchase->receipts->isNotEmpty();
@endphp

<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1>
            {{ $purchase->number ?: 'Compra #' . $purchase->id }}
        </h1>
        <p>Detalhes, produtos e acompanhamento da compra.</p>
    </div>
</div>


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

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Não foi possível concluir a operação:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if ($purchase->financialAccount)
    @php
        $account = $purchase->financialAccount;
        $paidAmount = (float) $account->transactions->sum('amount');

        $totalAmount = (float) $account->amount
            - (float) $account->discount
            + (float) $account->interest;

        $balance = max(0, $totalAmount - $paidAmount);
    @endphp

    <div class="content-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1">Conta a pagar</h5>
                <p class="text-muted mb-0">
                    Conta financeira vinculada a esta compra.
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
                <div class="text-muted small">Pago</div>
                <div class="fw-semibold text-success">
                    R$ {{ number_format($paidAmount, 2, ',', '.') }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Saldo a pagar</div>
                <div class="fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                    R$ {{ number_format($balance, 2, ',', '.') }}
                </div>
            </div>
        </div>

        @if ($account->status === \App\Models\FinancialAccount::STATUS_CANCELLED)
            <div class="alert alert-warning mt-3 mb-0">
                Esta conta a pagar foi cancelada.
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


<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="h5 mb-2">Informações da compra</h2>
            <span class="status-pill {{ $statusClasses[$purchase->status] ?? 'status-inactive' }}">
                {{ $statusLabels[$purchase->status] ?? $purchase->status }}
            </span>
        </div>

        <div class="text-end">
            <div class="text-muted small">Total da compra</div>
            <div class="fs-4 fw-semibold">
                R$ {{ number_format((float) $purchase->total, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="text-muted small">Fornecedor</div>
            <div class="fw-semibold">
                {{ $purchase->supplier->fantasy_name ?: $purchase->supplier->name }}
            </div>

            @if ($purchase->supplier->fantasy_name)
                <div class="text-muted small">{{ $purchase->supplier->name }}</div>
            @endif
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Data da compra</div>
            <div>{{ $purchase->purchase_date->format('d/m/Y') }}</div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Previsão de entrega</div>
            <div>
                {{ $purchase->expected_delivery_date?->format('d/m/Y') ?? 'Não informada' }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Número</div>
            <div>{{ $purchase->number ?: 'Não informado' }}</div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Criada em</div>
            <div>{{ $purchase->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Criada por</div>
            <div class="fw-semibold">
                {{ $creator?->user_name ?? 'Não identificado' }}
            </div>

            @if ($creator?->user_email)
                <div class="text-muted small">
                    {{ $creator->user_email }}
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Confirmada por</div>

            @if ($confirmationLog)
                <div class="fw-semibold">
                    {{ $confirmationLog->user_name ?? 'Usuário não identificado' }}
                </div>

                @if ($confirmationLog->user_email)
                    <div class="text-muted small">
                        {{ $confirmationLog->user_email }}
                    </div>
                @endif

                <div class="text-muted small">
                    {{ $confirmationLog->created_at->format('d/m/Y H:i') }}
                </div>
            @else
                <div class="text-muted">Ainda não confirmada</div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Última atualização</div>
            <div>{{ $purchase->updated_at->format('d/m/Y H:i') }}</div>
        </div>

        @if ($purchase->notes)
            <div class="col-12">
                <div class="text-muted small">Observações</div>
                <div class="text-break">{{ $purchase->notes }}</div>
            </div>
        @endif
    </div>
</div>


<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Anexos e comprovantes</h2>
            <p class="text-muted mb-0">
                Documentos relacionados a esta compra.
            </p>
        </div>

        <span class="badge bg-light text-dark">
            {{ $purchase->attachments->count() }}
            {{ $purchase->attachments->count() === 1 ? 'arquivo' : 'arquivos' }}
        </span>
    </div>

    @forelse ($purchase->attachments->sortByDesc('created_at') as $attachment)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-3 text-secondary">
                        <i class="bi {{ str_contains($attachment->mime_type ?? '', 'pdf')
                            ? 'bi-file-earmark-pdf'
                            : 'bi-file-earmark-image' }}"></i>
                    </div>

                    <div>
                        <div class="fw-semibold text-break">
                            {{ $attachment->original_name }}
                        </div>

                        <div class="text-muted small">
                            {{ number_format($attachment->file_size / 1024, 1, ',', '.') }} KB
                            ·
                            {{ $attachment->created_at->format('d/m/Y H:i') }}
                        </div>

                        <div class="text-muted small">
                            Enviado por:
                            {{ $attachment->uploader?->name ?? 'Usuário não disponível' }}
                        </div>

                        @if ($attachment->description)
                            <div class="small mt-1">
                                {{ $attachment->description }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a
                        href="{{ route('attachments.preview', $attachment) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-sm btn-outline-primary"
                    >
                        <i class="bi bi-eye"></i>
                        Visualizar
                    </a>

                    <a
                        href="{{ route('attachments.download', $attachment) }}"
                        class="btn btn-sm btn-outline-secondary"
                    >
                        <i class="bi bi-download"></i>
                        Baixar
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            <i class="bi bi-paperclip fs-3 d-block mb-2"></i>
            Nenhum comprovante anexado a esta compra.
        </div>
    @endforelse
</div>

<div class="content-card mb-4">
    <div class="mb-3">
        <h2 class="h5 mb-1">Produtos da compra</h2>
        <p class="text-muted mb-0">
            {{ $purchase->items->count() }}
            {{ $purchase->items->count() === 1 ? 'produto' : 'produtos' }}
        </p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th class="text-end">Quantidade</th>
                    <th class="text-end">Recebida</th>
                    <th class="text-end">Pendente</th>
                    <th class="text-end">Custo unitário</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($purchase->items as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->product->name }}</td>
                        <td>{{ $item->product->sku }}</td>
                        <td class="text-end">
                            {{ number_format((float) $item->quantity, 3, ',', '.') }}
                        </td>
                        <td class="text-end">
                            {{ number_format((float) $item->received_quantity, 3, ',', '.') }}
                        </td>
                        <td class="text-end">
                            {{ number_format($item->pendingQuantity(), 3, ',', '.') }}
                        </td>
                        <td class="text-end text-nowrap">
                            R$ {{ number_format((float) $item->unit_cost, 2, ',', '.') }}
                        </td>
                        <td class="text-end text-nowrap">
                            R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            Nenhum produto registrado nesta compra.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
            <div class="d-flex justify-content-between py-2">
                <span>Subtotal</span>
                <span>R$ {{ number_format((float) $purchase->subtotal, 2, ',', '.') }}</span>
            </div>

            <div class="d-flex justify-content-between py-2">
                <span>Desconto</span>
                <span>R$ {{ number_format((float) $purchase->discount, 2, ',', '.') }}</span>
            </div>

            <div class="d-flex justify-content-between border-top pt-3">
                <strong>Total</strong>
                <strong>R$ {{ number_format((float) $purchase->total, 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="mb-3">
        <h2 class="h5 mb-1">Histórico de recebimentos</h2>
        <p class="text-muted mb-0">
            Registro das entregas vinculadas a esta compra.
        </p>
    </div>

    @forelse ($purchase->receipts->sortByDesc('received_at') as $receipt)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">
                        Recebimento #{{ $receipt->id }}
                    </div>
                    <div class="text-muted small">
                        {{ $receipt->received_at->format('d/m/Y H:i') }}
                    </div>

                    @php
                        $receiptCreator = $receiptCreators->get((string) $receipt->id);
                    @endphp

                    <div class="text-muted small mt-1">
                        Registrado por:
                        <strong>
                            {{ $receiptCreator?->user_name ?? 'Usuário não identificado' }}
                        </strong>
                    </div>

                    @if ($receiptCreator?->user_email)
                        <div class="text-muted small">
                            {{ $receiptCreator->user_email }}
                        </div>
                    @endif
                </div>

                <span class="text-muted small">
                    {{ $receipt->items->count() }}
                    {{ $receipt->items->count() === 1 ? 'item' : 'itens' }}
                </span>
            </div>

            @if ($receipt->notes)
                <p class="mt-2 mb-0">{{ $receipt->notes }}</p>
            @endif

            <div class="table-responsive mt-3">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th class="text-end">Quantidade recebida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receipt->items as $receiptItem)
                            <tr>
                                <td>{{ $receiptItem->purchaseItem->product->name }}</td>
                                <td class="text-end">
                                    {{ number_format((float) $receiptItem->quantity, 3, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">
            Nenhum recebimento registrado até o momento.
        </p>
    @endforelse
    
    
    @if ($purchase->canReceive())
        <div class="mt-3">
            <a
                href="{{ route('goods-receipts.create', $purchase) }}"
                class="btn btn-primary"
            >
                Registrar recebimento
            </a>

            <p class="text-muted small mt-2 mb-0">
                O estoque será atualizado somente pelas quantidades registradas neste recebimento.
            </p>
        </div>
    @endif
</div>
<br>

{{-- VENCIMENTO DA CONTA A PAGAR --}}
@if ($purchase->status === \App\Models\Purchase::STATUS_DRAFT)
    <div class="content-card mb-4">
        <h5 class="mb-2">Vencimento da conta a pagar</h5>
        <p class="text-muted mb-3">
            Defina a data de vencimento que será utilizada na geração
            da conta a pagar ao confirmar esta compra.
        </p>

        <form
            action="{{ route('purchases.confirm', $purchase) }}"
            method="POST"
            id="confirm-purchase-form"
        >
            @csrf

            <div class="row">
                <div class="col-md-4">
                    <label for="due_date" class="form-label">
                        Data de vencimento
                    </label>

                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        class="form-control"
                        value="{{ old(
                            'due_date',
                            $purchase->expected_delivery_date?->format('Y-m-d')
                                ?? $purchase->purchase_date->format('Y-m-d')
                        ) }}"
                        min="{{ $purchase->purchase_date->format('Y-m-d') }}"
                        required
                    >

                    @error('due_date')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </form>
    </div>
@endif

{{-- AÇÕES DA COMPRA --}}
<div class="content-card mb-4">
    <h5 class="mb-2">Ações da compra</h5>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('purchases.index') }}"
           class="btn btn-outline-secondary">
            Voltar
        </a>

        @if ($canEdit)
            <a href="{{ route('purchases.edit', $purchase) }}"
               class="btn btn-outline-secondary">
                Editar
            </a>
        @endif

        @if ($purchase->status === \App\Models\Purchase::STATUS_DRAFT)
            <button
                type="submit"
                form="confirm-purchase-form"
                class="btn btn-primary"
                onclick="return confirm('Deseja confirmar esta compra?');"
            >
                Confirmar compra
            </button>
        @endif

        @if ($canCancel)
            <form
                action="{{ route('purchases.cancel', $purchase) }}"
                method="POST"
                onsubmit="return confirm('Deseja cancelar esta compra?');"
            >
                @csrf

                <button type="submit" class="btn btn-outline-danger">
                    Cancelar compra
                </button>
            </form>
        @endif
    </div>
</div>

@endsection