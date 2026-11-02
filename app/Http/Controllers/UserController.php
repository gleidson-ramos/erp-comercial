<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'role' => ['nullable', Rule::in(User::ROLES)],
            'status' => ['nullable', Rule::in(['ATIVO', 'INATIVO'])],
        ]);

        $users = User::query()
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->when($validated['role'] ?? null, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($validated['status'] ?? null, function ($query, $status) {
                $query->where(
                    'is_active',
                    $status === 'ATIVO'
                );
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => User::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(),
            'roles' => User::ROLES,
        ]);
    }

    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'can_manage_users' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::in($this->availablePermissions()),
            ],
        ]);

        $validated['is_active'] = true;

        $currentUser = $request->user();

        if (
            ! $currentUser->isAdmin()
            && $validated['role'] === User::ROLE_ADMIN
        ) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'role' => 'Somente um administrador pode cadastrar outro administrador.',
                ]);
        }

        $validated['can_manage_users'] = (
            $currentUser->isAdmin()
            && $validated['role'] === User::ROLE_MANAGER
        )
            ? $request->boolean('can_manage_users')
            : false;

        // Somente administradores podem conceder permissões.
        if ($currentUser->isAdmin() && $validated['role'] !== User::ROLE_ADMIN) {
            $validated['permissions'] = array_values(array_unique(
                $validated['permissions'] ?? []
            ));
        } else {
            $validated['permissions'] = [];
        }

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => User::ROLES,
        ]);
    }

    
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'can_manage_users' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::in($this->availablePermissions()),
            ],
        ]);

        $currentUser = $request->user();

        if (
            $currentUser->id === $user->id
            && $validated['role'] !== $user->role
        ) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'role' => 'Você não pode alterar seu próprio perfil.',
                ]);
        }

        $isRemovingAdmin = $user->is_active
            && $user->isAdmin()
            && $validated['role'] !== User::ROLE_ADMIN;

        if ($isRemovingAdmin && $this->isLastActiveAdmin($user)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'role' => 'O sistema precisa ter pelo menos um administrador ativo.',
                ]);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if (! $currentUser->isAdmin()) {
            if ($validated['role'] === User::ROLE_ADMIN) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'role' => 'Somente um administrador pode atribuir esse perfil.',
                    ]);
            }

            if (
                $user->can_manage_users
                && $user->role === User::ROLE_MANAGER
                && $validated['role'] !== User::ROLE_MANAGER
            ) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'role' => 'Somente um administrador pode alterar o perfil de um gerente autorizado.',
                    ]);
            }

            // Gerentes não podem alterar permissões individuais.
            unset($validated['permissions']);
            unset($validated['can_manage_users']);
        } else {
            $validated['can_manage_users'] = (
                $validated['role'] === User::ROLE_MANAGER
            )
                ? $request->boolean('can_manage_users')
                : false;

            // Administradores não precisam de permissões individuais.
            $validated['permissions'] = (
                $validated['role'] !== User::ROLE_ADMIN
            )
                ? array_values(array_unique($validated['permissions'] ?? []))
                : [];
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'user' => 'Você não pode desativar sua própria conta.',
            ]);
        }

        if ($user->is_active && $user->isAdmin()
            && $this->isLastActiveAdmin($user)) {
            return back()->withErrors([
                'user' => 'Não é possível desativar o último administrador ativo.',
            ]);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $message = $user->is_active
            ? 'Usuário ativado com sucesso.'
            : 'Usuário desativado com sucesso.';

        return back()->with('success', $message);
    }

    public function show(Request $request, User $user): View
    {
        $validated = $request->validate([
            'event' => ['nullable', 'in:created,updated,deleted'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        // Atividades realizadas pelo usuário.
        $activities = AuditLog::query()
            ->where('user_id', $user->id)
            ->when(
                $validated['event'] ?? null,
                fn ($query, $event) => $query->where('event', $event)
            )
            ->when(
                $validated['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $validated['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'activities_page')
            ->withQueryString();

        // Alterações realizadas no cadastro deste usuário,
        // independentemente de quem executou a operação.
        $profileChanges = AuditLog::query()
            ->where('auditable_type', $user->getMorphClass())
            ->where('auditable_id', (string) $user->getKey())
            ->when(
                $validated['event'] ?? null,
                fn ($query, $event) => $query->where('event', $event)
            )
            ->when(
                $validated['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $validated['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'profile_changes_page')
            ->withQueryString();

        // Indicadores das atividades realizadas pelo usuário.
        $activityCounts = [
            'total' => AuditLog::where('user_id', $user->id)->count(),

            'created' => AuditLog::where('user_id', $user->id)
                ->where('event', 'created')
                ->count(),

            'updated' => AuditLog::where('user_id', $user->id)
                ->where('event', 'updated')
                ->count(),

            'deleted' => AuditLog::where('user_id', $user->id)
                ->where('event', 'deleted')
                ->count(),
        ];

        return view('users.show', compact(
            'user',
            'activities',
            'profileChanges',
            'activityCounts'
        ));
    }

    private function isLastActiveAdmin(User $user): bool
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->doesntExist();
    }

    
    private function availablePermissions(): array
    {
        $permissions = [];

        foreach (config('permissions', []) as $group) {
            foreach ($group['permissions'] ?? [] as $permission => $label) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }
}