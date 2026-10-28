
@extends('layouts.app')

@section('title', 'Categorias')
@section('page-heading', 'Categorias')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS</span>
            <h1>Categorias</h1>
            <p>Organize os produtos por categoria.</p>
        </div>

        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nova categoria
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="content-card">
        <form method="GET" action="{{ route('categories.index') }}"
              class="row g-3 align-items-end mb-4">
            <div class="col-12 col-md-7">
                <label for="search" class="form-label">Pesquisar categoria</label>
                <input id="search" name="search" class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Nome da categoria">
            </div>

            <div class="col-12 col-md-3">
                <label for="status" class="form-label">Situação</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Ativas
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inativas
                    </option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1">Buscar</button>
                <a href="{{ route('categories.index') }}"
                   class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Produtos vinculados</th>
                        <th>Situação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>{{ $category->description ?: '—' }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                <span class="status-pill {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($category->is_active)
                                        <form method="POST"
                                              action="{{ route('categories.deactivate', $category) }}"
                                              onsubmit="return confirm('Deseja desativar esta categoria?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger"
                                                    title="Desativar">
                                                <i class="bi bi-toggle-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 d-block text-muted mb-2"></i>
                                Nenhuma categoria encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <small class="text-muted">
                {{ $categories->total() }} categoria(s)
            </small>
            {{ $categories->links() }}
        </div>
    </div>
@endsection