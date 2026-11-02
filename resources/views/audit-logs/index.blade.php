@extends('layouts.app')

@section('title', 'Auditoria')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Histórico de auditoria</h1>
            <p class="text-muted mb-0">
                Consulte as operações realizadas pelos usuários no sistema.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-logs.index') }}">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="search" class="form-label">Pesquisar</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Usuário, e-mail, módulo ou ID"
                        >
                    </div>

                    <div class="col-md-2">
                        <label for="event" class="form-label">Evento</label>
                        <select name="event" id="event" class="form-select">
                            <option value="">Todos</option>
                            <option value="created" @selected(request('event') === 'created')>
                                Criação
                            </option>
                            <option value="updated" @selected(request('event') === 'updated')>
                                Alteração
                            </option>
                            <option value="deleted" @selected(request('event') === 'deleted')>
                                Exclusão
                            </option>
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

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>
                            Filtrar
                        </button>

                        <a
                            href="{{ route('audit-logs.index') }}"
                            class="btn btn-outline-secondary"
                            title="Limpar filtros"
                        >
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Histórico --}}
    <div class="card shadow-sm">

        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Registros de auditoria</h5>

                <span class="badge bg-secondary">
                    {{ $auditLogs->total() }} registros
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Data e hora</th>
                            <th>Usuário</th>
                            <th>Módulo</th>
                            <th>Registro</th>
                            <th>Evento</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($auditLogs as $auditLog)

                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $auditLog->created_at?->format('d/m/Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $auditLog->created_at?->format('H:i:s') }}
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $auditLog->user_name ?? 'Usuário não identificado' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $auditLog->user_email ?? 'Sem e-mail registrado' }}
                                    </small>
                                </td>

                                <td>
                                    {{ class_basename($auditLog->auditable_type) }}
                                </td>

                                <td>
                                    #{{ $auditLog->auditable_id }}
                                </td>

                                <td>
                                    @switch($auditLog->event)
                                        @case('created')
                                            <span class="badge bg-success">Criação</span>
                                            @break

                                        @case('updated')
                                            <span class="badge bg-primary">Alteração</span>
                                            @break

                                        @case('deleted')
                                            <span class="badge bg-danger">Exclusão</span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary">
                                                {{ $auditLog->event }}
                                            </span>
                                    @endswitch
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('audit-logs.show', $auditLog) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-eye me-1"></i>
                                        Detalhes
                                    </a>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-journal-text fs-2 text-muted"></i>

                                    <p class="text-muted mt-2 mb-0">
                                        Nenhum registro de auditoria encontrado.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>

        @if ($auditLogs->hasPages())
            <div class="card-footer bg-white">
                {{ $auditLogs->links() }}
            </div>
        @endif

    </div>

</div>
@endsection