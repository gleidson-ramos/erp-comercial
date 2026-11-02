@extends('layouts.app')

@section('title', 'Acesso negado')
@section('page-heading', 'Acesso negado')

@section('content')
    <div class="content-card text-center py-5">
        <div class="mb-3">
            <i class="bi bi-shield-lock text-danger" style="font-size: 3rem;"></i>
        </div>

        <h2 class="h4 mb-2">Acesso não autorizado</h2>

        <p class="text-muted mb-4">
            Você não possui permissão para acessar esta página.
            Entre em contato com o administrador do sistema caso precise desse acesso.
        </p>

        <a href="{{ url('/') }}" class="btn btn-primary">
            Voltar ao início
        </a>
    </div>
@endsection