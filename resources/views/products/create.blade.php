@extends('layouts.app')

@section('title', 'Novo produto')
@section('page-heading', 'Novo produto')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / PRODUTOS</span>
            <h1>Novo produto</h1>
            <p>Preencha os dados para incluir um produto no catálogo.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form')
    </form>
@endsection