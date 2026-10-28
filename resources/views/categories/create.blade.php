
@extends('layouts.app')

@section('title', 'Nova categoria')
@section('page-heading', 'Nova categoria')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / CATEGORIAS</span>
            <h1>Nova categoria</h1>
            <p>Organize os produtos por grupos.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        @include('categories._form')
    </form>
@endsection