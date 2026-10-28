
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
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <label for="name" class="form-label">Nome da unidade *</label>
            <input
                id="name"
                name="name"
                class="form-control"
                value="{{ old('name', $unit->name ?? '') }}"
                maxlength="60"
                required
                placeholder="Ex.: Quilograma"
            >
        </div>

        <div class="col-12 col-md-4">
            <label for="abbreviation" class="form-label">Abreviação *</label>
            <input
                id="abbreviation"
                name="abbreviation"
                class="form-control"
                value="{{ old('abbreviation', $unit->abbreviation ?? '') }}"
                maxlength="10"
                required
                placeholder="Ex.: KG"
            >
            <small class="text-muted">Será armazenada em letras maiúsculas.</small>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('units.index') }}"
           class="btn btn-outline-secondary">Cancelar</a>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($unit) ? 'Salvar alterações' : 'Cadastrar unidade' }}
        </button>
    </div>
</div>