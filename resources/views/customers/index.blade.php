@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="page-heading">
    <div>
        <h1>Clientes</h1>
        <p class="text-muted mb-0">
            Gerencie os cadastros de clientes do estabelecimento.
        </p>
    </div>

    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>
        Novo cliente
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="content-card mb-4">
    <form method="GET" action="{{ route('customers.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Pesquisar</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Nome, CPF/CNPJ, telefone ou e-mail"
                >
            </div>

            <div class="col-md-3">
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

            <div class="col-md-2">
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

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('customers.index') }}"
                   class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Contato</th>
                    <th>Cidade/UF</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <small class="text-muted">
                                {{ $customer->person_type === 'PF' ? 'Pessoa física' : 'Pessoa jurídica' }}
                            </small>
                        </td>

                        <td>{{ $customer->formatted_document ?? '—' }}</td>

                        <td>
                            <div>{{ $customer->email ?: '—' }}</div>
                            <small class="text-muted">{{ $customer->phone }}</small>
                        </td>

                        <td>
                            @if($customer->city || $customer->state)
                                {{ $customer->city }}{{ $customer->city && $customer->state ? '/' : '' }}{{ $customer->state }}
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            @if($customer->is_active)
                                <span class="status-pill status-active">Ativo</span>
                            @else
                                <span class="status-pill status-inactive">Inativo</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <a
                                href="{{ route('customers.edit', $customer) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                <i class="bi bi-pencil"></i>
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhum cliente encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $customers->links() }}
    </div>
</div>
@endsection