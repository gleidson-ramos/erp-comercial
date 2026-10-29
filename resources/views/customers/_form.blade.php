@php
    $customer = $customer ?? null;
@endphp

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
    <div class="row g-3">
        <div class="col-md-3">
            <label for="person_type" class="form-label">Tipo de pessoa *</label>
            <select name="person_type" id="person_type" class="form-select" required>
                <option value="PF" @selected(old('person_type', $customer?->person_type ?? 'PF') === 'PF')>
                    Pessoa física
                </option>
                <option value="PJ" @selected(old('person_type', $customer?->person_type ?? '') === 'PJ')>
                    Pessoa jurídica
                </option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="name" class="form-label">Nome completo / Razão social *</label>
            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                maxlength="150"
                value="{{ old('name', $customer?->name) }}"
                required
            >
        </div>

        <div class="col-md-3">
            <label for="document" class="form-label" id="document-label">CPF *</label>
            <input
                type="text"
                name="document"
                id="document"
                class="form-control"
                value="{{ old('document', $customer?->document) }}"
                maxlength="18"
                autocomplete="off"
            >
            <small class="text-muted">Opcional. Se informado, será validado.</small>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                maxlength="150"
                value="{{ old('email', $customer?->email) }}"
            >
        </div>

        <div class="col-md-6">
            <label for="phone" class="form-label">Telefone</label>
            <input
                type="text"
                name="phone"
                id="phone"
                class="form-control"
                maxlength="20"
                value="{{ old('phone', $customer?->phone) }}"
            >
        </div>
    </div>

    <hr class="my-4">

    <h5 class="mb-3">Endereço</h5>

    <div class="row g-3">
        <div class="col-md-3">
            <label for="postal_code" class="form-label">CEP</label>
            <input
                type="text"
                name="postal_code"
                id="postal_code"
                class="form-control"
                maxlength="9"
                value="{{ old('postal_code', $customer?->postal_code) }}"
            >
        </div>

        <div class="col-md-7">
            <label for="street" class="form-label">Logradouro</label>
            <input
                type="text"
                name="street"
                id="street"
                class="form-control"
                maxlength="150"
                value="{{ old('street', $customer?->street) }}"
            >
        </div>

        <div class="col-md-2">
            <label for="number" class="form-label">Número</label>
            <input
                type="text"
                name="number"
                id="number"
                class="form-control"
                maxlength="20"
                value="{{ old('number', $customer?->number) }}"
            >
        </div>

        <div class="col-md-4">
            <label for="complement" class="form-label">Complemento</label>
            <input
                type="text"
                name="complement"
                id="complement"
                class="form-control"
                maxlength="100"
                value="{{ old('complement', $customer?->complement) }}"
            >
        </div>

        <div class="col-md-4">
            <label for="neighborhood" class="form-label">Bairro</label>
            <input
                type="text"
                name="neighborhood"
                id="neighborhood"
                class="form-control"
                maxlength="100"
                value="{{ old('neighborhood', $customer?->neighborhood) }}"
            >
        </div>

        <div class="col-md-3">
            <label for="city" class="form-label">Cidade</label>
            <input
                type="text"
                name="city"
                id="city"
                class="form-control"
                maxlength="100"
                value="{{ old('city', $customer?->city) }}"
            >
        </div>

        <div class="col-md-1">
            <label for="state" class="form-label">UF</label>
            <input
                type="text"
                name="state"
                id="state"
                class="form-control text-uppercase"
                maxlength="2"
                value="{{ old('state', $customer?->state) }}"
            >
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">Observações</label>
            <textarea
                name="notes"
                id="notes"
                rows="3"
                maxlength="2000"
                class="form-control"
            >{{ old('notes', $customer?->notes) }}</textarea>
        </div>

        @if($customer)
            <div class="col-12">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        class="form-check-input"
                        value="1"
                        @checked(old('is_active', $customer->is_active))
                    >
                    <label for="is_active" class="form-check-label">
                        Cliente ativo
                    </label>
                </div>
                <small class="text-muted">
                    Desmarque para desativar o cadastro.
                </small>
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            Salvar cliente
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('person_type');
    const label = document.getElementById('document-label');
    const documentInput = document.getElementById('document');
    const state = document.getElementById('state');

    function updateDocumentField() {
        const isCompany = type.value === 'PJ';

        label.textContent = isCompany ? 'CNPJ' : 'CPF';
        documentInput.placeholder = isCompany
            ? '00.000.000/0000-00'
            : '000.000.000-00';
    }

    type.addEventListener('change', updateDocumentField);

    state.addEventListener('input', function () {
        state.value = state.value.toUpperCase().replace(/[^A-Z]/g, '');
    });

    updateDocumentField();
});
</script>