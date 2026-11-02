@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Contas financeiras</h1>
        <p class="text-muted mb-0">
            Gerencie contas a pagar e a receber.
        </p>
    </div>

    <a href="{{ route('financial-accounts.create') }}"
       class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nova conta
    </a>
</div>

<div class="content-card mb-4">
    <form method="GET"
          action="{{ route('financial-accounts.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Pesquisar</label>
                <input type="text"
                       name="search"
                       id="search"
                       class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Descrição, documento ou cliente/fornecedor">
            </div>

            <div class="col-md-2">
                <label for="type" class="form-label">Tipo</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Todos</option>
                    <option value="PAGAR" @selected(request('type') === 'PAGAR')>
                        A pagar
                    </option>
                    <option value="RECEBER" @selected(request('type') === 'RECEBER')>
                        A receber
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Situação</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="ABERTA" @selected(request('status') === 'ABERTA')>
                        Aberta
                    </option>
                    <option value="PARCIAL" @selected(request('status') === 'PARCIAL')>
                        Parcial
                    </option>
                    <option value="PAGA" @selected(request('status') === 'PAGA')>
                        Paga
                    </option>
                    <option value="CANCELADA" @selected(request('status') === 'CANCELADA')>
                        Cancelada
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="due_from" class="form-label">Vencimento inicial</label>
                <input type="date"
                       name="due_from"
                       id="due_from"
                       class="form-control"
                       value="{{ request('due_from') }}">
            </div>

            <div class="col-md-2">
                <label for="due_to" class="form-label">Vencimento final</label>
                <input type="date"
                       name="due_to"
                       id="due_to"
                       class="form-control"
                       value="{{ request('due_to') }}">
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>

                <a href="{{ route('financial-accounts.index') }}"
                   class="btn btn-outline-secondary">
                    Limpar filtros
                </a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>Cliente / Fornecedor</th>
                    <th>Vencimento</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end">Saldo pendente</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $account)
                    @php
                        $paidCents = (int) round(
                            (float) ($account->transactions_sum_amount ?? 0) * 100
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

                        $statusClass = match ($account->status) {
                            'PAGA' => 'status-active',
                            'CANCELADA' => 'status-inactive',
                            'PARCIAL' => 'status-partial',
                            default => '',
                        };
                    @endphp

                    <tr>
                        <td>
                            <a href="{{ route('financial-accounts.show', $account) }}"
                               class="fw-semibold text-decoration-none">
                                {{ $account->description }}
                            </a>
                            @if ($account->document_number)
                                <div class="small text-muted">
                                    Doc.: {{ $account->document_number }}
                                </div>
                            @endif
                        </td>

                        <td>
                            {{ $account->type === 'PAGAR' ? 'A pagar' : 'A receber' }}
                        </td>

                        <td>{{ $party }}</td>

                        <td>{{ $account->due_date->format('d/m/Y') }}</td>

                        <td class="text-end text-nowrap">
                            R$ {{ number_format($totalCents / 100, 2, ',', '.') }}
                        </td>

                        <td class="text-end text-nowrap">
                            R$ {{ number_format($balanceCents / 100, 2, ',', '.') }}
                        </td>

                        <td>
                            <span class="status-pill {{ $statusClass }}">
                                {{ $account->status }}
                            </span>

                            @if (
                                $balanceCents > 0
                                && $account->due_date->lt(today())
                                && $account->status !== 'CANCELADA'
                            )
                                <div class="small text-danger mt-1">
                                    Vencida
                                </div>
                            @endif
                        </td>

                        <td class="text-end">
                            <a href="{{ route('financial-accounts.show', $account) }}"
                               class="btn btn-sm btn-outline-secondary">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Nenhuma conta financeira encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $accounts->links() }}
    </div>
</div>
@endsection