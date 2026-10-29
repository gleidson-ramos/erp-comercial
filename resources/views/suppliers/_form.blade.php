@php
    $editing = isset($supplier);
@endphp

<div class="content-card mb-4">
    <div class="mb-4">
        <h2 class="h5 mb-1">
            <i class="bi bi-building me-2"></i>Identificação
        </h2>
        <p class="text-muted small mb-0">
            Informe os dados de identificação do fornecedor.
        </p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <label for="person_type" class="form-label">Tipo de pessoa *</label>
            <select name="person_type" id="person_type"
                    class="form-select @error('person_type') is-invalid @enderror" required>
                <option value="">Selecione</option>
                <option value="PF" @selected(old('person_type', $supplier->person_type ?? '') === 'PF')>
                    Pessoa física
                </option>
                <option value="PJ" @selected(old('person_type', $supplier->person_type ?? '') === 'PJ')>
                    Pessoa jurídica
                </option>
            </select>
            @error('person_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-5">
            <label for="name" class="form-label">Nome ou razão social *</label>
            <input type="text" name="name" id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $supplier->name ?? '') }}"
                   maxlength="150" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="fantasy_name" class="form-label">Nome fantasia</label>
            <input type="text" name="fantasy_name" id="fantasy_name"
                   class="form-control @error('fantasy_name') is-invalid @enderror"
                   value="{{ old('fantasy_name', $supplier->fantasy_name ?? '') }}"
                   maxlength="150">
            @error('fantasy_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="document" class="form-label">CPF/CNPJ</label>
            <input type="text" name="document" id="document"
                   class="form-control @error('document') is-invalid @enderror"
                   value="{{ old('document', $supplier->document ?? '') }}"
                   maxlength="18" placeholder="Documento do fornecedor">
            @error('document')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $supplier->email ?? '') }}"
                   maxlength="150">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="phone" class="form-label">Telefone</label>
            <input type="text" name="phone" id="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $supplier->phone ?? '') }}"
                   maxlength="20">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="mb-4">
        <h2 class="h5 mb-1">
            <i class="bi bi-geo-alt me-2"></i>Endereço
        </h2>
        <p class="text-muted small mb-0">
            Informe o endereço principal do fornecedor. Os campos são opcionais.
        </p>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <label for="postal_code" class="form-label">CEP</label>
            <input type="text" name="postal_code" id="postal_code"
                   class="form-control @error('postal_code') is-invalid @enderror"
                   value="{{ old('postal_code', $supplier->postal_code ?? '') }}"
                   maxlength="9" placeholder="00000-000">
            @error('postal_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-7">
            <label for="street" class="form-label">Logradouro</label>
            <input type="text" name="street" id="street"
                   class="form-control @error('street') is-invalid @enderror"
                   value="{{ old('street', $supplier->street ?? '') }}"
                   maxlength="150">
            @error('street')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-2">
            <label for="number" class="form-label">Número</label>
            <input type="text" name="number" id="number"
                   class="form-control @error('number') is-invalid @enderror"
                   value="{{ old('number', $supplier->number ?? '') }}"
                   maxlength="20">
            @error('number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="complement" class="form-label">Complemento</label>
            <input type="text" name="complement" id="complement"
                   class="form-control @error('complement') is-invalid @enderror"
                   value="{{ old('complement', $supplier->complement ?? '') }}"
                   maxlength="100">
            @error('complement')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="neighborhood" class="form-label">Bairro</label>
            <input type="text" name="neighborhood" id="neighborhood"
                   class="form-control @error('neighborhood') is-invalid @enderror"
                   value="{{ old('neighborhood', $supplier->neighborhood ?? '') }}"
                   maxlength="100">
            @error('neighborhood')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="city" class="form-label">Cidade</label>
            <input type="text" name="city" id="city"
                   class="form-control @error('city') is-invalid @enderror"
                   value="{{ old('city', $supplier->city ?? '') }}"
                   maxlength="100">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-1">
            <label for="state" class="form-label">UF</label>
            <input type="text" name="state" id="state"
                   class="form-control @error('state') is-invalid @enderror"
                   value="{{ old('state', $supplier->state ?? '') }}"
                   maxlength="2">
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="mb-3">
        <h2 class="h5 mb-1">
            <i class="bi bi-card-text me-2"></i>Informações adicionais
        </h2>
        <p class="text-muted small mb-0">
            Registre observações relevantes para o relacionamento comercial.
        </p>
    </div>

    <div class="mb-3">
        <label for="notes" class="form-label">Observações</label>
        <textarea name="notes" id="notes" rows="4" maxlength="2000"
                  class="form-control @error('notes') is-invalid @enderror"
        >{{ old('notes', $supplier->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if ($editing)
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="form-check-input"
                   @checked((bool) old('is_active', $supplier->is_active))>
            <label for="is_active" class="form-check-label">
                Fornecedor ativo
            </label>
        </div>
    @endif
</div>

<div class="form-actions d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
        Cancelar
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>
        {{ $editing ? 'Salvar alterações' : 'Cadastrar fornecedor' }}
    </button>
</div>