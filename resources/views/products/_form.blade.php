@php
    $editing = isset($product);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Não foi possível salvar o produto.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="name" class="form-label">Nome do produto *</label>
            <input
                id="name"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $product->name ?? '') }}"
                maxlength="150"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-3">
            <label for="sku" class="form-label">SKU *</label>
            <input
                id="sku"
                name="sku"
                class="form-control @error('sku') is-invalid @enderror"
                value="{{ old('sku', $product->sku ?? '') }}"
                maxlength="50"
                required
            >
            @error('sku')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-3">
            <label for="barcode" class="form-label">Código de barras</label>
            <input
                id="barcode"
                name="barcode"
                class="form-control @error('barcode') is-invalid @enderror"
                value="{{ old('barcode', $product->barcode ?? '') }}"
                maxlength="100"
            >
            @error('barcode')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="category_id" class="form-label">Categoria</label>
            <select id="category_id" name="category_id" class="form-select">
                <option value="">Sem categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="unit_id" class="form-label">Unidade de medida *</label>
            <select id="unit_id" name="unit_id"
                    class="form-select @error('unit_id') is-invalid @enderror"
                    required>
                <option value="">Selecione uma unidade</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}"
                        @selected((string) old('unit_id', $product->unit_id ?? '') === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->abbreviation }})
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Descrição</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <div class="col-12 col-md-4">
            <label for="cost_price" class="form-label">Preço de custo (R$) *</label>
            <input
                type="number"
                id="cost_price"
                name="cost_price"
                class="form-control @error('cost_price') is-invalid @enderror"
                value="{{ old('cost_price', $product->cost_price ?? '0.00') }}"
                min="0"
                step="0.01"
                required
            >
            @error('cost_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="sale_price" class="form-label">Preço de venda (R$) *</label>
            <input
                type="number"
                id="sale_price"
                name="sale_price"
                class="form-control @error('sale_price') is-invalid @enderror"
                value="{{ old('sale_price', $product->sale_price ?? '') }}"
                min="0.01"
                step="0.01"
                required
            >
            @error('sale_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="minimum_stock" class="form-label">Estoque mínimo *</label>
            <input
                type="number"
                id="minimum_stock"
                name="minimum_stock"
                class="form-control @error('minimum_stock') is-invalid @enderror"
                value="{{ old('minimum_stock', $product->minimum_stock ?? '0') }}"
                min="0"
                step="0.001"
                required
            >
            @error('minimum_stock')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            Cancelar
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            {{ $editing ? 'Salvar alterações' : 'Cadastrar produto' }}
        </button>
    </div>
</div>