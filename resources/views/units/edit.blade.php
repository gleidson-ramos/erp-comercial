
@extends('layouts.app')

@section('title', 'Editar unidade')
@section('page-heading', 'Editar unidade')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / UNIDADES</span>
            <h1>Editar unidade</h1>
            <p>Atualize a unidade {{ $unit->name }}.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('units.update', $unit) }}">
        @csrf
        @method('PUT')
        @include('units._form')
    </form>
@endsection