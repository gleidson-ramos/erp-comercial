@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">CADASTROS</p>
        <h1>Fornecedores</h1>
        <p>Gerencie seus fornecedores e mantenha os dados comerciais atualizados.</p>
    </div>

    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>
        Novo fornecedor
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Fechar"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        Não foi possível concluir a operação. Confira os dados informados.
    </div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('suppliers.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-6">
                <label for="search" class="form-label">Pesquisar fornecedor</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Nome, nome fantasia, documento ou e-mail"
                    >
                </div>
            </div>

            <div class="col-lg-3 col-md-3">
                <label for="person_type" class="form-label">Tipo de pessoa</label>
                <select name="person_type" id="person_type" class="form-select">
                    <option value="">Todos</option>
                    <option value="PF" @selected(request('person_type') === 'PF')>
                        Pessoa física
                    </option>
                    <option value="PJ" @selected(request('person_type') === 'PJ')>
                        Pessoa jurídica
                    </option>
                </select>
            </div>

            <div class="col-lg-2 col-md-3">
                <label for="status" class="form-label">Situação</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Ativos
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inativos
                    </option>
                </select>
            </div>

            <div class="col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    Filtrar
                </button>
                <a href="{{ route('suppliers.index') }}"
                   class="btn btn-outline-secondary"
                   title="Limpar filtros">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Lista de fornecedores</h2>
            <p class="text-muted mb-0 small">
                {{ $suppliers->total() }}
                {{ $suppliers->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
            </p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Fornecedor</th>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Contato</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $supplier->name }}</div>
                            @if ($supplier->fantasy_name)
                                <div class="text-muted small">
                                    {{ $supplier->fantasy_name }}
                                </div>
                            @endif
                        </td>

                        <td>{{ $supplier->formatted_document ?: '—' }}</td>

                        <td>
                            {{ $supplier->person_type === 'PF'
                                ? 'Pessoa física'
                                : 'Pessoa jurídica' }}
                        </td>

                        <td>
                            @if ($supplier->email)
                                <div>{{ $supplier->email }}</div>
                            @endif
                            @if ($supplier->phone)
                                <div class="text-muted small">{{ $supplier->phone }}</div>
                            @endif
                            @if (!$supplier->email && !$supplier->phone)
                                —
                            @endif
                        </td>

                        <td>
                            @if ($supplier->is_active)
                                <span class="status-pill status-active">Ativo</span>
                            @else
                                <span class="status-pill status-inactive">Inativo</span>
                            @endif
                        </td>

                        <td class="text-end text-nowrap">
                            <a
                                href="{{ route('suppliers.edit', $supplier) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Editar fornecedor"
                            >
                                <i class="bi bi-pencil-square"></i>
                                <span class="d-none d-lg-inline">Editar</span>
                            </a>

                            @if ($supplier->is_active)
                                <form
                                    method="POST"
                                    action="{{ route('suppliers.deactivate', $supplier) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Deseja desativar este fornecedor?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Desativar fornecedor"
                                    >
                                        <i class="bi bi-person-x"></i>
                                        <span class="d-none d-lg-inline">Desativar</span>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-truck fs-2 d-block text-muted mb-2"></i>
                            <p class="fw-semibold mb-1">Nenhum fornecedor encontrado</p>
                            <p class="text-muted small mb-3">
                                Altere os filtros ou cadastre um novo fornecedor.
                            </p>
                            <a href="{{ route('suppliers.create') }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>
                                Cadastrar fornecedor
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($suppliers->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <small class="text-muted">
                Exibindo {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
                de {{ $suppliers->total() }} registros
            </small>

            {{ $suppliers->links() }}
        </div>
    @endif
</div>
@endsection