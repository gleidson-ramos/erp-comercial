@extends('layouts.app')

@section('title', 'Novo cliente')

@section('content')
<div class="page-heading">
    <div>
        <h1>Novo cliente</h1>
        <p class="text-muted mb-0">Cadastre uma pessoa física ou jurídica.</p>
    </div>
</div>

<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    @include('customers._form')
</form>
@endsection