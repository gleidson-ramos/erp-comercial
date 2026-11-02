<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'is_active',
    'can_manage_users',
    'permissions',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    public const ROLE_ADMIN = 'ADMINISTRADOR';
    public const ROLE_MANAGER = 'GERENTE';
    public const ROLE_SELLER = 'VENDEDOR';
    public const ROLE_FINANCIAL = 'FINANCEIRO';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_SELLER,
        self::ROLE_FINANCIAL,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_manage_users' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin()
            || (
                $this->role === self::ROLE_MANAGER
                && $this->can_manage_users
            );
    }

    /**
     * Verifica se o usuário possui determinada permissão.
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return in_array(
            $permission,
            $this->permissions ?? [],
            true
        );
    }

    /**
     * Verifica se possui ao menos uma das permissões informadas.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se possui todas as permissões informadas.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function accessibleNotificationTypes(): array
    {
        $types = [];

        if ($this->hasPermission('stock.view')) {
            $types[] = 'ESTOQUE_BAIXO';
        }

        if ($this->hasPermission('financial-accounts.view')) {
            $types[] = 'CONTA_VENCIDA';
            $types[] = 'CONTA_VENCENDO';
        }

        if ($this->hasPermission('purchases.view')) {
            $types[] = 'COMPRA_ATRASADA';
        }

        return $types;
    }
}