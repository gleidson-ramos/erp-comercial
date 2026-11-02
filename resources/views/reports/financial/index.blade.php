 @extends('layouts.app')

@section('title', 'Relatório Financeiro')
@section('page-heading', 'Relatório Financeiro')

@section('content')

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Relatório Financeiro</h1>
            <p class="text-muted mb-0">
                Contas a receber, contas a pagar e situação financeira.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a href="{{ route('reports.financial.pdf', request()->query()) }}"
            class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF
            </a>

            <a href="{{ route('reports.financial.export', request()->query()) }}"
            class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>
                Exportar Excel
            </a>

            <a href="{{ route('reports.index') }}"
            class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.financial.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Vencimento inicial</label>
                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            class="form-control"
                            value="{{ $filters['date_from'] }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Vencimento final</label>
                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            class="form-control"
                            value="{{ $filters['date_to'] }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="type" class="form-label">Tipo de conta</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Todos</option>
                            <option value="RECEBER" @selected($filters['type'] === 'RECEBER')>
                                A receber
                            </option>
                            <option value="PAGAR" @selected($filters['type'] === 'PAGAR')>
                                A pagar
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label">Situação</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todas</option>
                            <option value="ABERTA" @selected($filters['status'] === 'ABERTA')>
                                Aberta
                            </option>
                            <option value="PARCIAL" @selected($filters['status'] === 'PARCIAL')>
                                Parcial
                            </option>
                            <option value="PAGA" @selected($filters['status'] === 'PAGA')>
                                Paga
                            </option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>

                        <a href="{{ route('reports.financial.index') }}"
                           class="btn btn-outline-secondary">
                            Limpar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total a receber</div>
                    <h4 class="mb-0 text-success">
                        R$ {{ number_format($summary['total_receivable'], 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">
                        Pendente: R$ {{ number_format($summary['pending_receivable'], 2, ',', '.') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total a pagar</div>
                    <h4 class="mb-0 text-danger">
                        R$ {{ number_format($summary['total_payable'], 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">
                        Pendente: R$ {{ number_format($summary['pending_payable'], 2, ',', '.') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Já recebido</div>
                    <h4 class="mb-0 text-success">
                        R$ {{ number_format($summary['received'], 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">Transações vinculadas às contas</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Já pago</div>
                    <h4 class="mb-0 text-danger">
                        R$ {{ number_format($summary['paid'], 2, ',', '.') }}
                    </h4>
                    <small class="text-muted">Transações vinculadas às contas</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Inadimplência e parciais --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Contas a receber vencidas</div>
                    <h4 class="text-danger mb-0">
                        R$ {{ number_format($summary['overdue_receivable'], 2, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Contas a pagar vencidas</div>
                    <h4 class="text-danger mb-0">
                        R$ {{ number_format($summary['overdue_payable'], 2, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Contas parcialmente pagas</div>
                    <h4 class="mb-0">
                        {{ $summary['partial_count'] }}
                    </h4>
                    <small class="text-muted">
                        {{ $summary['overdue_count'] }} conta(s) vencida(s)
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- Listagem --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Contas financeiras</h5>
                <span class="text-muted small">
                    {{ $accounts->total() }} registro(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Vencimento</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Cliente / Fornecedor</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end">Pago</th>
                            <th class="text-end">Pendente</th>
                            <th>Situação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($accounts as $account)
                            @php
                                $paidAmount = (float) ($account->paid_amount ?? 0);
                                $pendingAmount = max(0, (float) $account->amount - $paidAmount);

                                $personName = $account->type === 'RECEBER'
                                    ? ($account->customer?->name ?? '-')
                                    : ($account->supplier?->name ?? '-');

                                $isOverdue = $account->due_date < now()->startOfDay()
                                    && $pendingAmount > 0;
                            @endphp

                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($account->due_date)->format('d/m/Y') }}

                                    @if($isOverdue)
                                        <span class="badge bg-danger ms-1">Vencida</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $account->description }}
                                    </div>

                                    @if($account->document_number)
                                        <small class="text-muted">
                                            Documento: {{ $account->document_number }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if($account->type === 'RECEBER')
                                        <span class="badge bg-success">A receber</span>
                                    @else
                                        <span class="badge bg-danger">A pagar</span>
                                    @endif
                                </td>

                                <td>{{ $personName }}</td>

                                <td class="text-end">
                                    R$ {{ number_format($account->amount, 2, ',', '.') }}
                                </td>

                                <td class="text-end text-success">
                                    R$ {{ number_format($paidAmount, 2, ',', '.') }}
                                </td>

                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($pendingAmount, 2, ',', '.') }}
                                </td>

                                <td>
                                    @switch($account->status)
                                        @case('ABERTA')
                                            <span class="badge bg-warning text-dark">Aberta</span>
                                            @break

                                        @case('PARCIAL')
                                            <span class="badge bg-info text-dark">Parcial</span>
                                            @break

                                        @case('PAGA')
                                            <span class="badge bg-success">Paga</span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary">
                                                {{ $account->status }}
                                            </span>
                                    @endswitch
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Nenhuma conta encontrada para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if($accounts->hasPages())
            <div class="card-footer bg-white">
                {{ $accounts->links() }}
            </div>
        @endif

    </div>

</div>
@endsection