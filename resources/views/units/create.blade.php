
@extends('layouts.app')

@section('title', 'Nova unidade')
@section('page-heading', 'Nova unidade')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">CADASTROS / UNIDADES</span>
            <h1>Nova unidade de medida</h1>
            <p>Cadastre as unidades utilizadas pelos produtos.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('units.store') }}">
        @csrf
        @include('units._form')
    </form>
@endsection