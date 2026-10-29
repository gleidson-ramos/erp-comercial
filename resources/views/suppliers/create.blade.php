@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">CADASTROS / FORNECEDORES</p>
        <h1>Novo fornecedor</h1>
        <p>Preencha as informações para incluir um fornecedor no ERP.</p>
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

<form method="POST" action="{{ route('suppliers.store') }}">
    @csrf
    @include('suppliers._form')
</form>
@endsection