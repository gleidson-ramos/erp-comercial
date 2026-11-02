@extends('layouts.app')

@section('content')
@php
    $paidCents = (int) round(
        (float) $account->transactions->sum('amount') * 100
    );

    $totalCents = (int) round(
        ((float) $account->amount
            - (float) $account->discount
            + (float) $account->interest) * 100
    );

    $balanceCents = max(0, $totalCents - $paidCents);

    $party = $account->type === 'RECEBER'
        ? ($account->customer?->name ?? '—')
        : ($account->supplier?->fantasy_name
            ?: $account->supplier?->name
            ?: '—');

    $operationLabel = $account->type === 'PAGAR'
        ? 'pagamento'
        : 'recebimento';
@endphp

<div class="page-heading">
    <div>
        <h1>Detalhes da conta</h1>
        <p class="text-muted mb-0">
            {{ $account->description }}
        </p>
    </div>

    <a href="{{ route('financial-accounts.index') }}"
       class="btn btn-outline-secondary">
        Voltar
    </a>
</div>

{{-- DADOS DA CONTA --}}
<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="text-muted small">Tipo de conta</div>
            <h5>
                {{ $account->type === 'PAGAR' ? 'Conta a pagar' : 'Conta a receber' }}
            </h5>
        </div>

        <span class="status-pill
            {{ $account->status === 'PAGA'
                ? 'status-active'
                : ($account->status === 'CANCELADA'
                    ? 'status-inactive'
                    : ($account->status === 'PARCIAL'
                        ? 'status-partial'
                        : '')) }}">
            {{ $account->status }}
        </span>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="text-muted small">
                {{ $account->type === 'RECEBER' ? 'Cliente' : 'Fornecedor' }}
            </div>
            <div class="fw-semibold">{{ $party }}</div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Documento</div>
            <div>{{ $account->document_number ?: '—' }}</div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Emissão</div>
            <div>{{ $account->issue_date->format('d/m/Y') }}</div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Criada por</div>

            @if ($accountCreator)
                <div class="fw-semibold">
                    {{ $accountCreator->user_name ?? 'Usuário não identificado' }}
                </div>

                @if ($accountCreator->user_email)
                    <div class="text-muted small">
                        {{ $accountCreator->user_email }}
                    </div>
                @endif

                <div class="text-muted small">
                    {{ $accountCreator->created_at->format('d/m/Y H:i') }}
                </div>
            @else
                <div class="text-muted">Não identificado</div>
            @endif
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Vencimento</div>
            <div>{{ $account->due_date->format('d/m/Y') }}</div>

            @if ($balanceCents > 0 && $account->due_date->lt(today()) && $account->status !== 'CANCELADA')
                <span class="text-danger small">Vencida</span>
            @endif
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Valor original</div>
            <div>R$ {{ number_format((float) $account->amount, 2, ',', '.') }}</div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Desconto</div>
            <div>R$ {{ number_format((float) $account->discount, 2, ',', '.') }}</div>
        </div>

        <div class="col-md-3">
            <div class="text-muted small">Juros / acréscimos</div>
            <div>R$ {{ number_format((float) $account->interest, 2, ',', '.') }}</div>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Total devido</div>
            <h5>R$ {{ number_format($totalCents / 100, 2, ',', '.') }}</h5>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Total pago / recebido</div>
            <h5>R$ {{ number_format($paidCents / 100, 2, ',', '.') }}</h5>
        </div>

        <div class="col-md-4">
            <div class="text-muted small">Saldo pendente</div>
            <h5>R$ {{ number_format($balanceCents / 100, 2, ',', '.') }}</h5>
        </div>

        @if ($account->notes)
            <div class="col-12">
                <div class="text-muted small">Observações</div>
                <div>{{ $account->notes }}</div>
            </div>
        @endif
    </div>
</div>

{{-- REGISTRO DE PAGAMENTO OU RECEBIMENTO --}}
@if (
    !in_array($account->status, ['PAGA', 'CANCELADA'], true)
    && $balanceCents > 0
)
    <div class="content-card mb-4">
        <h5 class="mb-3">
            {{ $account->type === 'PAGAR' ? 'Registrar pagamento' : 'Registrar recebimento' }}
        </h5>

        <form id="transactionForm"
              method="POST"
              action="{{ route('financial-accounts.transactions.store', $account) }}"
              enctype="multipart/form-data">

            @csrf

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="transaction_date" class="form-label">
                        Data *
                    </label>

                    <input type="date"
                           name="transaction_date"
                           id="transaction_date"
                           class="form-control @error('transaction_date') is-invalid @enderror"
                           value="{{ old('transaction_date', now()->toDateString()) }}"
                           required>

                    @error('transaction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="amount" class="form-label">
                        Valor (R$) *
                    </label>

                    <input type="number"
                           name="amount"
                           id="amount"
                           class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount', number_format($balanceCents / 100, 2, '.', '')) }}"
                           min="0.01"
                           max="{{ number_format($balanceCents / 100, 2, '.', '') }}"
                           step="0.01"
                           required>

                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="payment_method" class="form-label">
                        Forma de pagamento *
                    </label>

                    <select name="payment_method"
                            id="payment_method"
                            class="form-select @error('payment_method') is-invalid @enderror"
                            required>

                        <option value="PIX" @selected(old('payment_method', 'PIX') === 'PIX')>
                            PIX
                        </option>

                        <option value="DINHEIRO" @selected(old('payment_method') === 'DINHEIRO')>
                            Dinheiro
                        </option>

                        <option value="CARTAO" @selected(old('payment_method') === 'CARTAO')>
                            Cartão
                        </option>

                        <option value="TRANSFERENCIA" @selected(old('payment_method') === 'TRANSFERENCIA')>
                            Transferência
                        </option>

                        <option value="OUTRO" @selected(old('payment_method') === 'OUTRO')>
                            Outro
                        </option>
                    </select>

                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="reference" class="form-label">
                        Referência
                    </label>

                    <input type="text"
                           name="reference"
                           id="reference"
                           class="form-control @error('reference') is-invalid @enderror"
                           value="{{ old('reference') }}"
                           maxlength="100">

                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="transaction_notes" class="form-label">
                        Observações
                    </label>

                    <textarea name="notes"
                              id="transaction_notes"
                              class="form-control @error('notes') is-invalid @enderror"
                              rows="2"
                              maxlength="5000">{{ old('notes') }}</textarea>

                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <hr>

                    <h6 class="mb-1">Comprovantes da operação</h6>

                    <p class="text-muted small mb-2">
                        Anexe comprovantes de pagamento ou recebimento.
                        São permitidos até 10 arquivos de PDF ou imagem,
                        com limite de 10 MB por arquivo.
                    </p>

                    <label for="attachments" class="form-label">
                        Selecionar arquivos
                    </label>

                    <input type="file"
                           name="attachments[]"
                           id="attachments"
                           class="form-control @error('attachments') is-invalid @enderror"
                           accept=".pdf,.jpg,.jpeg,.png,.webp"
                           multiple>

                    @error('attachments')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    @foreach ($errors->getMessages() as $key => $messages)
                        @if (str_starts_with($key, 'attachments.'))
                            @foreach ($messages as $message)
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <div class="col-12">
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmTransactionModal">

                        {{ $account->type === 'PAGAR' ? 'Registrar pagamento' : 'Registrar recebimento' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- MODAL DE CONFIRMAÇÃO --}}
    <div class="modal fade"
         id="confirmTransactionModal"
         tabindex="-1"
         aria-labelledby="confirmTransactionModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTransactionModalLabel">
                        Confirmar {{ $operationLabel }}
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>

                <div class="modal-body">
                    <p>
                        Confirma o registro deste {{ $operationLabel }}?
                    </p>

                    <div class="bg-light rounded p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Operação</span>
                            <strong>
                                {{ $account->type === 'PAGAR' ? 'Pagamento' : 'Recebimento' }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Valor</span>
                            <strong id="confirmationAmount">—</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Data</span>
                            <strong id="confirmationDate">—</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Forma</span>
                            <strong id="confirmationMethod">—</strong>
                        </div>
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Após a confirmação, a operação será registrada
                        e os comprovantes serão vinculados a ela.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Voltar e revisar
                    </button>

                    <button type="submit"
                            form="transactionForm"
                            class="btn btn-primary">
                        Confirmar e registrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif


{{-- Histórico --}}
<div class="content-card">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="mb-1">Histórico de movimentações</h5>
            <small class="text-muted">
                Transações registradas no período selecionado.
            </small>
        </div>

        <span class="badge bg-light text-dark">
            {{ $account->transactions->count() }} registros
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">

            <thead>
                <tr>
                    <th>Data da operação</th>
                    <th>Descrição</th>
                    <th>Cliente / Fornecedor</th>
                    <th>Forma de pagamento</th>
                    <th>Tipo</th>
                    <th>Registrado por</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>

            <tbody>
                @forelse($account->transactions as $transaction)

                    @php
                        $account = $transaction->financialAccount;

                        $isReceipt = $account?->type ===
                            \App\Models\FinancialAccount::TYPE_RECEIVABLE;

                        $relatedParty = $account?->customer?->name
                            ?? $account?->supplier?->fantasy_name
                            ?? $account?->supplier?->name
                            ?? '-';

                        $transactionCreator = $transactionCreators->get(
                            (string) $transaction->id
                        );
                    @endphp

                    <tr>
                        <td>
                            {{ $transaction->transaction_date->format('d/m/Y') }}
                        </td>

                        <td>
                            <div class="fw-medium">
                                {{ $account?->description ?? 'Conta financeira' }}
                            </div>

                            @if($transaction->reference)
                                <small class="text-muted">
                                    {{ $transaction->reference }}
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $relatedParty }}
                        </td>

                        <td>
                            {{ $transaction->payment_method }}
                        </td>

                        <td>
                            @if($isReceipt)
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    Recebimento
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    Pagamento
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($transactionCreator)
                                <div>{{ $transactionCreator->user_name ?? 'Usuário não identificado' }}</div>

                                @if ($transactionCreator->user_email)
                                    <small class="text-muted">
                                        {{ $transactionCreator->user_email }}
                                    </small>
                                @endif

                                <small class="text-muted d-block">
                                    {{ $transactionCreator->created_at?->format('d/m/Y H:i') }}
                                </small>
                            @else
                                <span class="text-muted">Não identificado</span>
                            @endif
                        </td>

                        <td class="text-end fw-semibold {{ $isReceipt ? 'text-success' : 'text-danger' }}">
                            {{ $isReceipt ? '+' : '−' }}
                            R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox fs-2 text-muted"></i>

                            <p class="text-muted mb-0 mt-2">
                                Nenhuma movimentação encontrada.
                            </p>
                        </td>
                    </tr>

                @endforelse
            </tbody>

        </table>
    </div>


</div>

{{-- ATUALIZAÇÃO DOS DADOS DO MODAL --}}
@if (
    !in_array($account->status, ['PAGA', 'CANCELADA'], true)
    && $balanceCents > 0
)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const amountInput = document.getElementById('amount');
            const dateInput = document.getElementById('transaction_date');
            const methodInput = document.getElementById('payment_method');

            const confirmationAmount = document.getElementById('confirmationAmount');
            const confirmationDate = document.getElementById('confirmationDate');
            const confirmationMethod = document.getElementById('confirmationMethod');

            const modal = document.getElementById('confirmTransactionModal');

            if (!modal) {
                return;
            }

            modal.addEventListener('show.bs.modal', function () {
                const amount = parseFloat(amountInput.value || 0);

                confirmationAmount.textContent = amount.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });

                if (dateInput.value) {
                    const [year, month, day] = dateInput.value.split('-');
                    confirmationDate.textContent = `${day}/${month}/${year}`;
                } else {
                    confirmationDate.textContent = 'Não informada';
                }

                confirmationMethod.textContent =
                    methodInput.options[methodInput.selectedIndex]?.text || '—';
            });
        });
    </script>
@endif

@endsection