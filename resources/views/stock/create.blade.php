@extends('layouts.app')

@section('title', 'Nova movimentação de estoque')

@section('content')
<div class="page-heading">
    <div>
        <h1>Nova movimentação</h1>
        <p class="text-muted mb-0">
            Registre uma entrada ou saída de produtos.
        </p>
    </div>

    <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
        Voltar ao estoque
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Verifique os campos informados:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <form method="POST" action="{{ route('stock.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="type" class="form-label">Tipo de movimentação *</label>
                <select name="type" id="type" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="IN" @selected(old('type') === 'IN')>
                        Entrada
                    </option>
                    <option value="OUT" @selected(old('type') === 'OUT')>
                        Saída
                    </option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="product_id" class="form-label">Produto *</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Selecione um produto</option>

                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            @selected((string) old('product_id') === (string) $product->id)
                        >
                            {{ $product->name }}
                            — SKU: {{ $product->sku }}
                            @if($product->unit)
                                ({{ $product->unit->abbreviation }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="quantity" class="form-label">Quantidade *</label>
                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    class="form-control"
                    min="0.001"
                    max="99999999999.999"
                    step="0.001"
                    value="{{ old('quantity') }}"
                    required
                >
            </div>

            <div class="col-md-4" id="unit-cost-field">
                <label for="unit_cost" class="form-label">
                    Custo unitário *
                </label>
                <input
                    type="number"
                    name="unit_cost"
                    id="unit_cost"
                    class="form-control"
                    min="0"
                    max="9999999999.99"
                    step="0.01"
                    value="{{ old('unit_cost') }}"
                >
                <small class="text-muted">
                    Obrigatório para entradas.
                </small>
            </div>

            <div class="col-md-4">
                <label for="reason" class="form-label">Motivo *</label>
                <select name="reason" id="reason" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="COMPRA" @selected(old('reason') === 'COMPRA')>
                        Compra
                    </option>
                    <option value="VENDA" @selected(old('reason') === 'VENDA')>
                        Venda
                    </option>
                    <option value="DEVOLUCAO" @selected(old('reason') === 'DEVOLUCAO')>
                        Devolução
                    </option>
                    <option value="AJUSTE" @selected(old('reason') === 'AJUSTE')>
                        Ajuste de estoque
                    </option>
                    <option value="PERDA" @selected(old('reason') === 'PERDA')>
                        Perda
                    </option>
                    <option value="USO_INTERNO" @selected(old('reason') === 'USO_INTERNO')>
                        Uso interno
                    </option>
                    <option value="OUTRO" @selected(old('reason') === 'OUTRO')>
                        Outro
                    </option>
                </select>
            </div>

            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea
                    name="notes"
                    id="notes"
                    rows="3"
                    maxlength="2000"
                    class="form-control"
                    placeholder="Informações adicionais sobre a movimentação"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Registrar movimentação
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('type');
    const costField = document.getElementById('unit-cost-field');
    const costInput = document.getElementById('unit_cost');

    function updateCostField() {
        const isEntry = type.value === 'IN';

        costField.hidden = !isEntry;
        costInput.required = isEntry;

        if (!isEntry) {
            costInput.value = '';
        }
    }

    type.addEventListener('change', updateCostField);
    updateCostField();
});
</script>
@endsection