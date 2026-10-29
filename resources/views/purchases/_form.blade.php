@php
    $isEdit = isset($purchase);

    $selectedItems = old(
        'items',
        $isEdit
            ? $purchase->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
            ])->values()->toArray()
            : [['product_id' => '', 'quantity' => '1.000', 'unit_cost' => '0.00']]
    );

    $productOptions = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
    ])->values()->toArray();
@endphp


<div class="content-card">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="supplier_id" class="form-label">Fornecedor *</label>
            <select
                name="supplier_id"
                id="supplier_id"
                class="form-select @error('supplier_id') is-invalid @enderror"
                required
            >
                <option value="">Selecione um fornecedor</option>

                @foreach ($suppliers as $supplier)
                    <option
                        value="{{ $supplier->id }}"
                        @selected((string) old('supplier_id', $purchase->supplier_id ?? '') === (string) $supplier->id)
                    >
                        {{ $supplier->fantasy_name ?: $supplier->name }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="number" class="form-label">Número da compra</label>
            <input
                type="text"
                name="number"
                id="number"
                class="form-control @error('number') is-invalid @enderror"
                value="{{ old('number', $purchase->number ?? '') }}"
                maxlength="30"
                placeholder="Opcional"
            >
            @error('number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="purchase_date" class="form-label">Data da compra *</label>
            <input
                type="date"
                name="purchase_date"
                id="purchase_date"
                class="form-control @error('purchase_date') is-invalid @enderror"
                value="{{ old('purchase_date', isset($purchase) ? $purchase->purchase_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                required
            >
            @error('purchase_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="expected_delivery_date" class="form-label">Previsão de entrega</label>
            <input
                type="date"
                name="expected_delivery_date"
                id="expected_delivery_date"
                class="form-control @error('expected_delivery_date') is-invalid @enderror"
                value="{{ old('expected_delivery_date', isset($purchase) ? $purchase->expected_delivery_date?->format('Y-m-d') : '') }}"
            >
            @error('expected_delivery_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-8">
            <label for="notes" class="form-label">Observações</label>
            <textarea
                name="notes"
                id="notes"
                class="form-control @error('notes') is-invalid @enderror"
                rows="2"
                maxlength="5000"
            >{{ old('notes', $purchase->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Produtos da compra</h2>
            <p class="text-muted mb-0">Informe os produtos, quantidades e custos unitários.</p>
        </div>

        <button type="button" id="add-purchase-item" class="btn btn-outline-secondary">
            Adicionar produto
        </button>
    </div>

    @error('items')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="table-responsive">
        <table class="table align-middle" id="purchase-items-table">
            <thead>
                <tr>
                    <th style="min-width: 250px;">Produto *</th>
                    <th style="min-width: 130px;">Quantidade *</th>
                    <th style="min-width: 150px;">Custo unitário (R$) *</th>
                    <th style="min-width: 140px;" class="text-end">Subtotal (R$)</th>
                    <th class="text-end">Ação</th>
                </tr>
            </thead>

            <tbody id="purchase-items-body">
                @foreach ($selectedItems as $index => $item)
                    <tr class="purchase-item-row">
                        <td>
                            <select
                                name="items[{{ $index }}][product_id]"
                                class="form-select product-select"
                                required
                            >
                                <option value="">Selecione um produto</option>
                                @foreach ($products as $product)
                                    <option
                                        value="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                    >
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][quantity]"
                                class="form-control item-quantity"
                                min="0.001"
                                max="99999999999.999"
                                step="0.001"
                                value="{{ $item['quantity'] ?? '1.000' }}"
                                required
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][unit_cost]"
                                class="form-control item-unit-cost"
                                min="0"
                                max="9999999999.99"
                                step="0.01"
                                value="{{ $item['unit_cost'] ?? '0.00' }}"
                                required
                            >
                        </td>

                        <td class="text-end text-nowrap">
                            <span class="item-subtotal">R$ 0,00</span>
                        </td>

                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary remove-purchase-item"
                                aria-label="Remover produto"
                                title="Remover produto"
                            >
                                Remover
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end mt-3">
        <div class="col-md-5 col-lg-4">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <strong id="purchase-subtotal">R$ 0,00</strong>
            </div>

            <div class="mb-3">
                <label for="discount" class="form-label">Desconto total (R$)</label>
                <input
                    type="number"
                    name="discount"
                    id="discount"
                    class="form-control @error('discount') is-invalid @enderror"
                    min="0"
                    step="0.01"
                    value="{{ old('discount', $purchase->discount ?? '0.00') }}"
                >
                @error('discount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between border-top pt-3">
                <span class="fw-semibold">Total da compra</span>
                <strong id="purchase-total" class="fs-5">R$ 0,00</strong>
            </div>
        </div>
    </div>
</div>

<div class="form-actions mt-3">
    <a
        href="{{ $isEdit ? route('purchases.show', $purchase) : route('purchases.index') }}"
        class="btn btn-outline-secondary"
    >
        Cancelar
    </a>

    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'Salvar alterações' : 'Salvar rascunho' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('purchase-items-body');
    const addButton = document.getElementById('add-purchase-item');
    const discountInput = document.getElementById('discount');
    const subtotalElement = document.getElementById('purchase-subtotal');
    const totalElement = document.getElementById('purchase-total');

    let nextIndex = {{ count($selectedItems) }};

    function toCents(value) {
        const number = Number(value);

        if (!Number.isFinite(number)) {
            return 0;
        }

        return Math.round(number * 100);
    }

    function money(cents) {
        return (cents / 100).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function calculate() {
        let subtotalCents = 0;

        tbody.querySelectorAll('.purchase-item-row').forEach(function (row) {
            const quantity = Number(row.querySelector('.item-quantity').value) || 0;
            const unitCostCents = toCents(row.querySelector('.item-unit-cost').value);
            const quantityMilli = Math.round(quantity * 1000);
            const lineCents = Math.round(quantityMilli * unitCostCents / 1000);

            subtotalCents += lineCents;
            row.querySelector('.item-subtotal').textContent = money(lineCents);
        });

        const discountCents = Math.max(0, toCents(discountInput.value));
        const totalCents = Math.max(0, subtotalCents - discountCents);

        subtotalElement.textContent = money(subtotalCents);
        totalElement.textContent = money(totalCents);
    }

    function updateProductOptions() {
        const selects = Array.from(tbody.querySelectorAll('.product-select'));
        const selectedValues = selects.map(select => select.value).filter(Boolean);

        selects.forEach(function (select) {
            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                option.disabled =
                    selectedValues.includes(option.value) &&
                    select.value !== option.value;
            });
        });
    }

    function addRow() {
        const index = nextIndex++;
        const options = {{ Illuminate\Support\Js::from($productOptions) }};
        const row = document.createElement('tr');
        row.className = 'purchase-item-row';

        const productCell = document.createElement('td');
        const select = document.createElement('select');
        select.name = `items[${index}][product_id]`;
        select.className = 'form-select product-select';
        select.required = true;

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione um produto';
        select.appendChild(placeholder);

        options.forEach(function (product) {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} (${product.sku})`;
            select.appendChild(option);
        });

        productCell.appendChild(select);

        const quantityCell = document.createElement('td');
        const quantity = document.createElement('input');
        quantity.type = 'number';
        quantity.name = `items[${index}][quantity]`;
        quantity.className = 'form-control item-quantity';
        quantity.min = '0.001';
        quantity.max = '99999999999.999';
        quantity.step = '0.001';
        quantity.value = '1.000';
        quantity.required = true;
        quantityCell.appendChild(quantity);

        const costCell = document.createElement('td');
        const cost = document.createElement('input');
        cost.type = 'number';
        cost.name = `items[${index}][unit_cost]`;
        cost.className = 'form-control item-unit-cost';
        cost.min = '0';
        cost.max = '9999999999.99';
        cost.step = '0.01';
        cost.value = '0.00';
        cost.required = true;
        costCell.appendChild(cost);

        const subtotalCell = document.createElement('td');
        subtotalCell.className = 'text-end text-nowrap';
        const subtotal = document.createElement('span');
        subtotal.className = 'item-subtotal';
        subtotal.textContent = 'R$ 0,00';
        subtotalCell.appendChild(subtotal);

        const actionCell = document.createElement('td');
        actionCell.className = 'text-end';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-outline-secondary remove-purchase-item';
        removeButton.textContent = 'Remover';
        removeButton.setAttribute('aria-label', 'Remover produto');
        actionCell.appendChild(removeButton);

        row.append(productCell, quantityCell, costCell, subtotalCell, actionCell);
        tbody.appendChild(row);

        updateProductOptions();
        calculate();
    }

    addButton.addEventListener('click', addRow);

    tbody.addEventListener('input', calculate);

    tbody.addEventListener('change', function (event) {
        if (event.target.classList.contains('product-select')) {
            updateProductOptions();
        }

        calculate();
    });

    tbody.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-purchase-item');

        if (!button) {
            return;
        }

        if (tbody.querySelectorAll('.purchase-item-row').length <= 1) {
            alert('A compra deve conter pelo menos um produto.');
            return;
        }

        button.closest('.purchase-item-row').remove();
        updateProductOptions();
        calculate();
    });

    discountInput.addEventListener('input', calculate);

    updateProductOptions();
    calculate();
});
</script>