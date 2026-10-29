@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Editar venda</h1>
        <p>Altere os dados da venda enquanto ela estiver em rascunho.</p>
    </div>
</div>

<form action="{{ route('sales.update', $sale) }}" method="POST">
    @csrf
    @method('PUT')

    @include('sales._form')
</form>
@endsection