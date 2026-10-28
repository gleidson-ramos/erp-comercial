
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Verifique os campos informados:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <div class="mb-3">
        <label for="name" class="form-label">Nome da categoria *</label>
        <input
            id="name"
            name="name"
            class="form-control"
            value="{{ old('name', $category->name ?? '') }}"
            maxlength="100"
            required
        >
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Descrição</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="form-control"
        >{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="form-actions">
        <a href="{{ route('categories.index') }}"
           class="btn btn-outline-secondary">Cancelar</a>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($category) ? 'Salvar alterações' : 'Cadastrar categoria' }}
        </button>
    </div>
</div>