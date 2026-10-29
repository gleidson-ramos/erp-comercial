@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h1>Editar compra</h1>
    <p>
        Atualize os dados e os produtos da compra
        {{ $purchase->number ?: '#' . $purchase->id }}.
    </p>
</div>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">
            Verifique os campos abaixo:
        </div>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('purchases.update', $purchase) }}" method="POST">
    @csrf
    @method('PUT')

    @include('purchases._form')
</form>
@endsection