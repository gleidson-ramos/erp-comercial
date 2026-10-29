@php
    $isEdit = isset($sale);

    $selectedItems = old(
        'items',
        $isEdit
            ? $sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'discount' => $item->discount,
            ])->values()->toArray()
            : [['product_id' => '', 'quantity' => '1', 'discount' => '0.00']]
    );

    $productOptions = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->sale_price,
    ])->values()->toArray();
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Verifique os dados informados:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="customer_id" class="form-label">Cliente</label>

            <select name="customer_id" id="customer_id" class="form-select">
                <option value="">Consumidor não identificado</option>

                @foreach ($customers as $customer)
                    <option
                        value="{{ $customer->id }}"
                        @selected((string) old('customer_id', $sale->customer_id ?? '') === (string) $customer->id)
                    >
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="number" class="form-label">Número da venda</label>

            <input
                type="text"
                name="number"
                id="number"
                class="form-control"
                maxlength="30"
                value="{{ old('number', $sale->number ?? '') }}"
                placeholder="Gerado ou informado manualmente"
            >
        </div>

        <div class="col-md-4">
            <label for="sale_date" class="form-label">Data da venda *</label>

            <input
                type="date"
                name="sale_date"
                id="sale_date"
                class="form-control"
                required
                value="{{ old('sale_date', isset($sale) ? $sale->sale_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            >
        </div>
    </div>

    <h5 class="mb-3">Produtos da venda</h5>

    <div class="table-responsive">
        <table class="table align-middle" id="sale-items-table">
            <thead>
                <tr>
                    <th style="min-width: 230px;">Produto</th>
                    <th style="min-width: 110px;">Quantidade</th>
                    <th style="min-width: 130px;">Preço unitário</th>
                    <th style="min-width: 130px;">Desconto</th>
                    <th style="min-width: 130px;">Subtotal</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="sale-items-body">
                @foreach ($selectedItems as $index => $item)
                    <tr class="sale-item-row">
                        <td>
                            <select
                                name="items[{{ $index }}][product_id]"
                                class="form-select sale-product"
                                required
                            >
                                <option value="">Selecione</option>

                                @foreach ($products as $product)
                                    <option
                                        value="{{ $product->id }}"
                                        @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                    >
                                        {{ $product->name }} — R$
                                        {{ number_format((float) $product->sale_price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][quantity]"
                                class="form-control sale-quantity"
                                min="0.001"
                                max="99999999999.999"
                                step="0.001"
                                required
                                value="{{ $item['quantity'] ?? '' }}"
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control sale-unit-price"
                                value="0,00"
                                readonly
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][discount]"
                                class="form-control sale-discount"
                                min="0"
                                max="9999999999.99"
                                step="0.01"
                                value="{{ $item['discount'] ?? '0.00' }}"
                            >
                        </td>

                        <td>
                            <span class="sale-line-total">R$ 0,00</span>
                        </td>

                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary remove-sale-item"
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

    <button type="button" class="btn btn-outline-primary mb-4" id="add-sale-item">
        <i class="bi bi-plus-lg"></i>
        Adicionar produto
    </button>

    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal dos itens</span>
                <strong id="sale-subtotal">R$ 0,00</strong>
            </div>

            <div class="mb-3">
                <label for="discount" class="form-label">Desconto geral (R$)</label>

                <input
                    type="number"
                    name="discount"
                    id="discount"
                    class="form-control"
                    min="0"
                    max="999999999999.99"
                    step="0.01"
                    value="{{ old('discount', $sale->discount ?? '0.00') }}"
                >
            </div>

            <div class="d-flex justify-content-between border-top pt-3">
                <span class="fw-bold">Total da venda</span>
                <strong id="sale-total" class="fs-5">R$ 0,00</strong>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <label for="notes" class="form-label">Observações</label>

        <textarea
            name="notes"
            id="notes"
            rows="3"
            maxlength="5000"
            class="form-control"
        >{{ old('notes', $sale->notes ?? '') }}</textarea>
    </div>

    <div class="form-actions mt-4">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            Voltar
        </a>

        <button type="submit" class="btn btn-primary">
            Salvar rascunho
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('sale-items-body');
    const addButton = document.getElementById('add-sale-item');
    const discountInput = document.getElementById('discount');

    const products = {{ Illuminate\Support\Js::from($productOptions) }};

    const money = (cents) => (cents / 100).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });

    const toCents = (value) => {
        const number = Number(value || 0);
        return Number.isFinite(number) ? Math.round(number * 100) : 0;
    };

    function selectedProductIds(exceptRow = null) {
        return [...body.querySelectorAll('.sale-product')]
            .filter(select => select.closest('tr') !== exceptRow)
            .map(select => select.value)
            .filter(Boolean);
    }

    function refreshProductOptions() {
        const selectedIds = selectedProductIds();

        body.querySelectorAll('.sale-item-row').forEach(row => {
            const select = row.querySelector('.sale-product');

            [...select.options].forEach(option => {
                if (!option.value) return;

                option.disabled =
                    selectedIds.includes(option.value) &&
                    option.value !== select.value;
            });
        });
    }

    function calculate() {
        let subtotalCents = 0;

        body.querySelectorAll('.sale-item-row').forEach(row => {
            const select = row.querySelector('.sale-product');
            const product = products.find(p => String(p.id) === select.value);

            const quantity = Number(row.querySelector('.sale-quantity').value || 0);
            const priceCents = product ? toCents(product.price) : 0;
            const quantityMilli = Number.isFinite(quantity)
                ? Math.round(quantity * 1000)
                : 0;

            const grossCents = Math.round(
                quantityMilli * priceCents / 1000
            );

            const itemDiscount = Math.max(
                0,
                toCents(row.querySelector('.sale-discount').value)
            );

            const lineCents = Math.max(0, grossCents - itemDiscount);

            row.querySelector('.sale-unit-price').value =
                (priceCents / 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

            row.querySelector('.sale-line-total').textContent =
                money(lineCents);

            subtotalCents += lineCents;
        });

        const discountCents = Math.max(
            0,
            toCents(discountInput.value)
        );

        document.getElementById('sale-subtotal').textContent =
            money(subtotalCents);

        document.getElementById('sale-total').textContent =
            money(Math.max(0, subtotalCents - discountCents));
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', calculate);
            input.addEventListener('change', () => {
                refreshProductOptions();
                calculate();
            });
        });

        row.querySelector('.remove-sale-item').addEventListener('click', () => {
            row.remove();

            if (!body.querySelector('.sale-item-row')) {
                addRow();
            }

            refreshProductOptions();
            calculate();
        });
    }

    function addRow() {
        const index = Date.now() + Math.floor(Math.random() * 1000);

        const row = document.createElement('tr');
        row.className = 'sale-item-row';

        const options = products.map(product =>
            `<option value="${product.id}">${escapeHtml(product.name)} — ${
                money(toCents(product.price))
            }</option>`
        ).join('');

        row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]"
                    class="form-select sale-product" required>
                    <option value="">Selecione</option>
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]"
                    class="form-control sale-quantity"
                    min="0.001" max="99999999999.999"
                    step="0.001" value="1" required>
            </td>
            <td>
                <input type="text" class="form-control sale-unit-price"
                    value="0,00" readonly>
            </td>
            <td>
                <input type="number" name="items[${index}][discount]"
                    class="form-control sale-discount"
                    min="0" max="9999999999.99"
                    step="0.01" value="0.00">
            </td>
            <td><span class="sale-line-total">R$ 0,00</span></td>
            <td>
                <button type="button"
                    class="btn btn-sm btn-outline-secondary remove-sale-item">
                    Remover
                </button>
            </td>
        `;

        body.appendChild(row);
        bindRow(row);
        refreshProductOptions();
        calculate();
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);
    }

    addButton.addEventListener('click', addRow);
    discountInput.addEventListener('input', calculate);

    body.querySelectorAll('.sale-item-row').forEach(bindRow);

    refreshProductOptions();
    calculate();
});
</script>