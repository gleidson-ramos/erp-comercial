@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Nova compra</h1>
        <p>Cadastre o fornecedor, os produtos e os valores da compra.</p>
    </div>
</div>

<form action="{{ route('purchases.store') }}" method="POST">
    @csrf

    @include('purchases._form')
</form>
@endsection