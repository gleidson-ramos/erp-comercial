 @extends('layouts.app')

@section('title', 'Detalhes do usuário')
@section('page-heading', 'Detalhes do usuário')

@section('content')
<div class="container-fluid px-0">

    {{-- Cabeçalho --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Detalhes do usuário</h4>
            <p class="text-muted mb-0">
                Informações cadastrais e histórico de atividades.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>
                Editar usuário
            </a>
        </div>
    </div>

    {{-- Informações cadastrais --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                     style="width: 64px; height: 64px; font-size: 1.5rem;">
                    <i class="bi bi-person"></i>
                </div>

                <div>
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <div class="text-muted">{{ $user->email }}</div>
                </div>

                <div class="ms-md-auto">
                    @if ($user->is_active)
                        <span class="badge bg-success-subtle text-success px-3 py-2">
                            Ativo
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                            Inativo
                        </span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row g-4">
                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">Nome</small>
                    <strong>{{ $user->name }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">E-mail</small>
                    <strong>{{ $user->email }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">Perfil de acesso</small>
                    <strong>{{ ucfirst(strtolower($user->role)) }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">Gerenciar usuários</small>
                    <strong>
                        {{ $user->can_manage_users ? 'Sim' : 'Não' }}
                    </strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">Data de cadastro</small>
                    <strong>{{ $user->created_at?->format('d/m/Y H:i') ?? 'Não informado' }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block mb-1">Última atualização</small>
                    <strong>{{ $user->updated_at?->format('d/m/Y H:i') ?? 'Não informado' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Total de atividades</small>
                    <h3 class="fw-bold mb-0 mt-2">
                        {{ $activityCounts['total'] }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Registros criados</small>
                    <h3 class="fw-bold text-success mb-0 mt-2">
                        {{ $activityCounts['created'] }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Registros alterados</small>
                    <h3 class="fw-bold text-primary mb-0 mt-2">
                        {{ $activityCounts['updated'] }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Registros excluídos</small>
                    <h3 class="fw-bold text-danger mb-0 mt-2">
                        {{ $activityCounts['deleted'] }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Histórico de atividades --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <div class="mb-4">
                <h5 class="fw-bold mb-1">Histórico de atividades</h5>
                <p class="text-muted mb-0">
                    Operações realizadas por este usuário no ERP.
                </p>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('users.show', $user) }}" class="row g-3 mb-4">

                <div class="col-md-4">
                    <label for="event" class="form-label">Tipo de atividade</label>
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

                <div class="col-md-3">
                    <label for="date_from" class="form-label">Data inicial</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        class="form-control"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div class="col-md-3">
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
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i>
                        Filtrar
                    </button>

                    <a href="{{ route('users.show', $user) }}"
                       class="btn btn-outline-secondary"
                       title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Data e hora</th>
                            <th>Módulo</th>
                            <th>Registro</th>
                            <th>Atividade</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td>
                                    {{ $activity->created_at?->format('d/m/Y H:i:s') }}
                                </td>

                                <td>
                                    {{ class_basename($activity->auditable_type) }}
                                </td>

                                <td>
                                    #{{ $activity->auditable_id }}
                                </td>

                                <td>
                                    @if ($activity->event === 'created')
                                        <span class="badge bg-success-subtle text-success">
                                            Criação
                                        </span>
                                    @elseif ($activity->event === 'updated')
                                        <span class="badge bg-primary-subtle text-primary">
                                            Alteração
                                        </span>
                                    @elseif ($activity->event === 'deleted')
                                        <span class="badge bg-danger-subtle text-danger">
                                            Exclusão
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ $activity->event }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('audit-logs.show', $activity) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                    Nenhuma atividade encontrada para este usuário.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $activities->links() }}
            </div>

        </div>
    </div>

    {{-- Alterações realizadas no cadastro do usuário --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">

            <div class="mb-4">
                <h5 class="fw-bold mb-1">Alterações no cadastro</h5>
                <p class="text-muted mb-0">
                    Histórico de operações realizadas por qualquer usuário
                    sobre o cadastro de {{ $user->name }}.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Data e hora</th>
                            <th>Responsável</th>
                            <th>Atividade</th>
                            <th>IP</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($profileChanges as $change)
                            <tr>
                                <td>
                                    {{ $change->created_at?->format('d/m/Y H:i:s') }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $change->user_name ?? 'Usuário não identificado' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $change->user_email ?? 'E-mail não disponível' }}
                                    </small>
                                </td>

                                <td>
                                    @if ($change->event === 'created')
                                        <span class="badge bg-success-subtle text-success">
                                            Criação
                                        </span>
                                    @elseif ($change->event === 'updated')
                                        <span class="badge bg-primary-subtle text-primary">
                                            Alteração
                                        </span>
                                    @elseif ($change->event === 'deleted')
                                        <span class="badge bg-danger-subtle text-danger">
                                            Exclusão
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $change->ip_address ?? 'Não informado' }}
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('audit-logs.show', $change) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Visualizar alteração"
                                    >
                                        <i class="bi bi-eye me-1"></i>
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-person-check fs-3 d-block mb-2"></i>
                                    Nenhuma alteração encontrada no cadastro deste usuário.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $profileChanges->links() }}
            </div>

        </div>
    </div>

</div>
@endsection