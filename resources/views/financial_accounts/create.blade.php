@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <h1>Nova conta financeira</h1>
        <p class="text-muted mb-0">
            Cadastre uma obrigação a pagar ou um valor a receber.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('financial-accounts.store') }}">
    @csrf
    @include('financial_accounts._form')
</form>
@endsection