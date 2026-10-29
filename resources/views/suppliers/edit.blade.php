@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">CADASTROS / FORNECEDORES</p>
        <h1>Editar fornecedor</h1>
        <p>Atualize as informações cadastrais e a situação do fornecedor.</p>
    </div>

    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Voltar
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-1"></i>
        Existem campos que precisam ser corrigidos. Confira as mensagens abaixo.
    </div>
@endif

<div class="content-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="h5 mb-1">{{ $supplier->name }}</h2>
            <p class="text-muted mb-0 small">
                Código #{{ $supplier->id }}
                @if ($supplier->fantasy_name)
                    · {{ $supplier->fantasy_name }}
                @endif
            </p>
        </div>

        @if ($supplier->is_active)
            <span class="status-pill status-active">Ativo</span>
        @else
            <span class="status-pill status-inactive">Inativo</span>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
    @csrf
    @method('PUT')

    @include('suppliers._form')
</form>
@endsection