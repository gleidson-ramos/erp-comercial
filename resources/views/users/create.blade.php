@extends('layouts.app')

@section('title', 'Novo usuário')
@section('page-heading', 'Novo usuário')

@section('content')
<div class="page-heading">
    <h1 class="h4 mb-1">Novo usuário</h1>
    <p class="text-muted mb-0">Cadastre um novo acesso ao ERP.</p>
</div>

<div class="content-card">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        @include('users._form')
    </form>
</div>
@endsection