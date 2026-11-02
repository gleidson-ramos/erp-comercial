@php
    $selectedType = old('type', $type ?? 'PAGAR');
@endphp

<div class="content-card">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="type" class="form-label">Tipo de conta *</label>
            <select name="type"
                    id="type"
                    class="form-select @error('type') is-invalid @enderror"
                    required>
                <option value="PAGAR" @selected($selectedType === 'PAGAR')>
                    Conta a pagar
                </option>
                <option value="RECEBER" @selected($selectedType === 'RECEBER')>
                    Conta a receber
                </option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4" id="customer-field">
            <label for="customer_id" class="form-label">Cliente *</label>
            <select name="customer_id"
                    id="customer_id"
                    class="form-select @error('customer_id') is-invalid @enderror">
                <option value="">Selecione um cliente</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}"
                        @selected((string) old('customer_id') === (string) $customer->id)>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4" id="supplier-field">
            <label for="supplier_id" class="form-label">Fornecedor *</label>
            <select name="supplier_id"
                    id="supplier_id"
                    class="form-select @error('supplier_id') is-invalid @enderror">
                <option value="">Selecione um fornecedor</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                        @selected((string) old('supplier_id') === (string) $supplier->id)>
                        {{ $supplier->fantasy_name ?: $supplier->name }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-8">
            <label for="description" class="form-label">Descrição *</label>
            <input type="text"
                   name="description"
                   id="description"
                   class="form-control @error('description') is-invalid @enderror"
                   value="{{ old('description') }}"
                   maxlength="200"
                   required>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="document_number" class="form-label">Número do documento</label>
            <input type="text"
                   name="document_number"
                   id="document_number"
                   class="form-control"
                   value="{{ old('document_number') }}"
                   maxlength="50">
        </div>

        <div class="col-md-4">
            <label for="issue_date" class="form-label">Data de emissão *</label>
            <input type="date"
                   name="issue_date"
                   id="issue_date"
                   class="form-control @error('issue_date') is-invalid @enderror"
                   value="{{ old('issue_date', now()->toDateString()) }}"
                   required>
            @error('issue_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="due_date" class="form-label">Data de vencimento *</label>
            <input type="date"
                   name="due_date"
                   id="due_date"
                   class="form-control @error('due_date') is-invalid @enderror"
                   value="{{ old('due_date') }}"
                   required>
            @error('due_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="amount" class="form-label">Valor original (R$) *</label>
            <input type="number"
                   name="amount"
                   id="amount"
                   class="form-control @error('amount') is-invalid @enderror"
                   value="{{ old('amount') }}"
                   min="0.01"
                   step="0.01"
                   required>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="discount" class="form-label">Desconto (R$)</label>
            <input type="number"
                   name="discount"
                   id="discount"
                   class="form-control @error('discount') is-invalid @enderror"
                   value="{{ old('discount', '0.00') }}"
                   min="0"
                   step="0.01">
            @error('discount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="interest" class="form-label">Juros / acréscimos (R$)</label>
            <input type="number"
                   name="interest"
                   id="interest"
                   class="form-control @error('interest') is-invalid @enderror"
                   value="{{ old('interest', '0.00') }}"
                   min="0"
                   step="0.01">
            @error('interest')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">Observações</label>
            <textarea name="notes"
                      id="notes"
                      class="form-control"
                      rows="3"
                      maxlength="5000">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

<div class="form-actions mt-3">
    <a href="{{ route('financial-accounts.index') }}"
       class="btn btn-outline-secondary">
        Cancelar
    </a>

    <button type="submit" class="btn btn-primary">
        Salvar conta
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('type');
    const customerField = document.getElementById('customer-field');
    const supplierField = document.getElementById('supplier-field');
    const customer = document.getElementById('customer_id');
    const supplier = document.getElementById('supplier_id');

    function updateFields() {
        const receiving = type.value === 'RECEBER';

        customerField.classList.toggle('d-none', !receiving);
        supplierField.classList.toggle('d-none', receiving);

        customer.required = receiving;
        supplier.required = !receiving;

        customer.disabled = !receiving;
        supplier.disabled = receiving;
    }

    type.addEventListener('change', updateFields);
    updateFields();
});
</script>