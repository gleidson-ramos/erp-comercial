
@extends('layouts.app')

@section('title', 'Editar categoria')
@section('page-heading', 'Editar categoria')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / CATEGORIAS</span>
            <h1>Editar categoria</h1>
            <p>Atualize os dados de {{ $category->name }}.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('categories._form')
    </form>
@endsection