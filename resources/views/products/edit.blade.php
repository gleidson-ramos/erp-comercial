@extends('layouts.app')

@section('title', 'Editar produto')
@section('page-heading', 'Editar produto')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / PRODUTOS</span>
            <h1>Editar produto</h1>
            <p>Atualize os dados de {{ $product->name }}.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')

        @include('products._form')
    </form>
@endsection