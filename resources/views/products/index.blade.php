@extends('layouts.app')

@section('title', 'Produtos')
@section('page-heading', 'Produtos')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / PRODUTOS</span>
            <h1>Produtos</h1>
            <p>Gerencie o catálogo de produtos do seu negócio.</p>
        </div>

        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Novo produto
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
        <form method="GET" action="{{ route('products.index') }}"
              class="row g-3 align-items-end mb-4">
            <div class="col-12 col-lg-7">
                <label for="search" class="form-label">Pesquisar</label>
                <input
                    type="search"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Nome, SKU ou código de barras"
                >
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label for="status" class="form-label">Situação</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Ativos
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inativos
                    </option>
                </select>
            </div>

            <div class="col-12 col-md-6 col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
                <a href="{{ route('products.index') }}"
                   class="btn btn-outline-secondary" title="Limpar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>SKU</th>
                        <th>Categoria</th>
                        <th>Unidade</th>
                        <th class="text-end">Custo</th>
                        <th class="text-end">Venda</th>
                        <th>Situação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if ($product->barcode)
                                    <small class="d-block text-muted">
                                        {{ $product->barcode }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->category?->name ?? 'Sem categoria' }}</td>
                            <td>{{ $product->unit->abbreviation }}</td>
                            <td class="text-end">
                                R$ {{ number_format((float) $product->cost_price, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <strong>
                                    R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                @if ($product->is_active)
                                    <span class="status-pill status-active">Ativo</span>
                                @else
                                    <span class="status-pill status-inactive">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($product->is_active)
                                        <form
                                            method="POST"
                                            action="{{ route('products.deactivate', $product) }}"
                                            onsubmit="return confirm('Deseja desativar este produto?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-sm btn-outline-danger"
                                                    title="Desativar">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-2 d-block text-muted mb-2"></i>
                                <strong>Nenhum produto encontrado</strong>
                                <p class="text-muted mb-0 mt-1">
                                    Cadastre um produto ou ajuste os filtros.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
            <small class="text-muted">
                {{ $products->total() }} produto(s) encontrado(s)
            </small>

            {{ $products->links() }}
        </div>
    </div>
@endsection