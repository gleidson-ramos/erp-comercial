@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<div class="page-heading">
    <div>
        <h1>Editar cliente</h1>
        <p class="text-muted mb-0">
            Atualize os dados de {{ $customer->name }}.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('customers.update', $customer) }}">
    @csrf
    @method('PUT')
    @include('customers._form')
</form>
@endsection