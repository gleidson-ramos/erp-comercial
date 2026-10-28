
@extends('layouts.app')

@section('title', 'Unidades de medida')
@section('page-heading', 'Unidades')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS</span>
            <h1>Unidades de medida</h1>
            <p>Gerencie as unidades usadas nos produtos.</p>
        </div>

        <a href="{{ route('units.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nova unidade
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="content-card">
        <form method="GET" action="{{ route('units.index') }}"
              class="row g-3 align-items-end mb-4">
            <div class="col-12 col-md-7">
                <label for="search" class="form-label">Pesquisar unidade</label>
                <input id="search" name="search" class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Nome ou abreviação">
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
                <a href="{{ route('units.index') }}"
                   class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Abreviação</th>
                        <th>Produtos vinculados</th>
                        <th>Situação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td><strong>{{ $unit->name }}</strong></td>
                            <td>{{ $unit->abbreviation }}</td>
                            <td>{{ $unit->products_count }}</td>
                            <td>
                                <span class="status-pill {{ $unit->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $unit->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('units.edit', $unit) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($unit->is_active)
                                        <form method="POST"
                                              action="{{ route('units.deactivate', $unit) }}"
                                              onsubmit="return confirm('Deseja desativar esta unidade?')">
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
                                Nenhuma unidade encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <small class="text-muted">
                {{ $units->total() }} unidade(s)
            </small>
            {{ $units->links() }}
        </div>
    </div>
@endsection