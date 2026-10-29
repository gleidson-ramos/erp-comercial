@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Nova venda</h1>
        <p>Registre os produtos e os valores da venda.</p>
    </div>
</div>

<form action="{{ route('sales.store') }}" method="POST">
    @csrf

    @include('sales._form')
</form>
@endsection