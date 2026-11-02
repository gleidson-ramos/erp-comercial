@extends('layouts.app')

@section('title', 'Editar usuário')
@section('page-heading', 'Editar usuário')

@section('content')
<div class="page-heading">
    <h1 class="h4 mb-1">Editar usuário</h1>
    <p class="text-muted mb-0">
        Atualize os dados de {{ $user->name }}.
    </p>
</div>

<div class="content-card">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')

        @include('users._form')
    </form>
</div>
@endsection