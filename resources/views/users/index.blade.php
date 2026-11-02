@extends('layouts.app')

@section('title', 'Usuários')
@section('page-heading', 'Gestão de usuários')

@section('content')
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="h4 mb-1">Usuários</h1>
        <p class="text-muted mb-0">Gerencie os acessos ao ERP Comercial.</p>
    </div>

    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>
        Novo usuário
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('users.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Pesquisar</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Nome ou e-mail"
                >
            </div>

            <div class="col-md-3">
                <label for="role" class="form-label">Perfil</label>
                <select name="role" id="role" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Situação</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="ATIVO" @selected(request('status') === 'ATIVO')>
                        Ativos
                    </option>
                    <option value="INATIVO" @selected(request('status') === 'INATIVO')>
                        Inativos
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="section-heading mb-3">
        <div>
            <h5 class="mb-1">Usuários cadastrados</h5>
            <p class="text-muted mb-0">
                {{ $users->total() }} usuário(s) encontrado(s).
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Perfil</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                {{ $user->name }}

                                @if (auth()->id() === $user->id)
                                    <span class="badge bg-light text-dark">Você</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $user->email }}</small>
                        </td>

                        <td>{{ $user->role }}</td>

                        <td>
                            @if ($user->is_active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a
                                    href="{{ route('users.show', $user) }}"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Visualizar informações"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a
                                    href="{{ route('users.edit', $user) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Editar"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if (auth()->id() !== $user->id)
                                    <form
                                        method="POST"
                                        action="{{ route('users.toggle-status', $user) }}"
                                        onsubmit="return confirm('Deseja realmente {{ $user->is_active ? 'desativar' : 'ativar' }} este usuário?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            title="{{ $user->is_active ? 'Desativar' : 'Ativar' }}"
                                        >
                                            <i class="bi {{ $user->is_active ? 'bi-person-x' : 'bi-person-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection