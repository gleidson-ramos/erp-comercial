
@php
    $isEditing = $user->exists;

    $permissionGroups = config('permissions', []);

    $selectedPermissions = old(
        'permissions',
        $user->permissions ?? []
    );

    $selectedPermissions = is_array($selectedPermissions)
        ? $selectedPermissions
        : [];

    $isAdminUser = old('role', $user->role) === \App\Models\User::ROLE_ADMIN;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nome completo *</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}"
            maxlength="255"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">E-mail *</label>
        <input
            type="email"
            name="email"
            id="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}"
            maxlength="255"
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Perfil de acesso *</label>

        <select
            name="role"
            id="role"
            class="form-select @error('role') is-invalid @enderror"
            required
        >
            <option value="">Selecione um perfil</option>

            @foreach ($roles as $role)
                <option
                    value="{{ $role }}"
                    @selected(old('role', $user->role) === $role)
                >
                    {{ $role }}
                </option>
            @endforeach
        </select>

        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if (auth()->user()->isAdmin())
        <div
            class="col-12"
            id="user-permission-field"
            style="{{ old('role', $user->role) === \App\Models\User::ROLE_MANAGER ? '' : 'display: none;' }}"
        >
            <div class="form-check form-switch">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="can_manage_users"
                    id="can_manage_users"
                    value="1"
                    @checked(old('can_manage_users', $user->can_manage_users))
                >

                <label class="form-check-label" for="can_manage_users">
                    Pode cadastrar e gerenciar usuários
                </label>
            </div>

            <small class="text-muted">
                Permite que este gerente cadastre, edite e gerencie outros usuários.
            </small>
        </div>
    @endif

    <div class="col-md-6">
        <label for="password" class="form-label">
            Senha {{ $isEditing ? '(deixe em branco para manter)' : '*' }}
        </label>

        <input
            type="password"
            name="password"
            id="password"
            class="form-control @error('password') is-invalid @enderror"
            autocomplete="new-password"
            minlength="8"
            {{ $isEditing ? '' : 'required' }}
        >

        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">
            Confirmar senha {{ $isEditing ? '(se alterar)' : '*' }}
        </label>

        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-control"
            autocomplete="new-password"
            minlength="8"
            {{ $isEditing ? '' : 'required' }}
        >
    </div>
</div>

{{-- Permissões individuais --}}
@if (auth()->user()->isAdmin())
    <div
        class="mt-4"
        id="permissions-section"
        style="{{ $isAdminUser ? 'display: none;' : '' }}"
    >
        <hr>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Permissões de acesso</h5>
                <p class="text-muted mb-0">
                    Defina quais módulos e operações este usuário poderá acessar.
                </p>
            </div>
        </div>

        @error('permissions')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @foreach ($permissionGroups as $module => $group)
            <div class="border rounded p-3 mb-3 permission-group">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <strong>{{ $group['label'] }}</strong>

                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input select-all-permissions"
                            id="select-all-{{ $module }}"
                            data-module="{{ $module }}"
                        >

                        <label
                            class="form-check-label small"
                            for="select-all-{{ $module }}"
                        >
                            Selecionar todas
                        </label>
                    </div>
                </div>

                <div class="row g-2">
                    @foreach ($group['permissions'] as $permission => $label)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input permission-checkbox"
                                    name="permissions[]"
                                    id="permission-{{ str_replace('.', '-', $permission) }}"
                                    value="{{ $permission }}"
                                    data-module="{{ $module }}"
                                    @checked(in_array($permission, $selectedPermissions, true))
                                >

                                <label
                                    class="form-check-label"
                                    for="permission-{{ str_replace('.', '-', $permission) }}"
                                >
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('permissions.*')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        @endforeach

        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Administradores possuem acesso irrestrito. As permissões individuais
            são aplicadas aos demais perfis.
        </div>
    </div>
@endif

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        Cancelar
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>
        {{ $isEditing ? 'Salvar alterações' : 'Cadastrar usuário' }}
    </button>
</div>

@if (auth()->user()->isAdmin())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleField = document.getElementById('role');

            const managerField = document.getElementById(
                'user-permission-field'
            );

            const permissionsSection = document.getElementById(
                'permissions-section'
            );

            function updateRoleVisibility() {
                if (!roleField) {
                    return;
                }

                const isManager = roleField.value === 'GERENTE';
                const isAdmin = roleField.value === 'ADMINISTRADOR';

                if (managerField) {
                    managerField.style.display = isManager ? '' : 'none';

                    if (!isManager) {
                        const checkbox = document.getElementById(
                            'can_manage_users'
                        );

                        if (checkbox) {
                            checkbox.checked = false;
                        }
                    }
                }

                if (permissionsSection) {
                    permissionsSection.style.display = isAdmin ? 'none' : '';
                }
            }

            function updateModuleCheckbox(module) {
                const checkboxes = document.querySelectorAll(
                    '.permission-checkbox[data-module="' + module + '"]'
                );

                const selectAll = document.querySelector(
                    '.select-all-permissions[data-module="' + module + '"]'
                );

                if (!selectAll || !checkboxes.length) {
                    return;
                }

                const checkedCount = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .length;

                selectAll.checked = checkedCount === checkboxes.length;

                selectAll.indeterminate =
                    checkedCount > 0 &&
                    checkedCount < checkboxes.length;
            }

            document.querySelectorAll('.select-all-permissions')
                .forEach(selectAll => {
                    selectAll.addEventListener('change', function () {
                        const module = this.dataset.module;

                        document.querySelectorAll(
                            '.permission-checkbox[data-module="' + module + '"]'
                        ).forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });

                    updateModuleCheckbox(selectAll.dataset.module);
                });

            document.querySelectorAll('.permission-checkbox')
                .forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        updateModuleCheckbox(this.dataset.module);
                    });
                });

            roleField?.addEventListener('change', updateRoleVisibility);

            updateRoleVisibility();
        });
    </script>
@endif
