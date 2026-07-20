<?php

namespace App\Libraries;

use Config\Permissions;

class Authorization
{
    private Permissions $config;

    public function __construct(?Permissions $config = null)
    {
        $this->config = $config ?? config(Permissions::class);
    }

    public function roleName(): string
    {
        return trim((string) (
            session()->get('role_name')
            ?? session()->get('role')
            ?? ''
        ));
    }

    public function roleKey(): string
    {
        $roleName = mb_strtolower($this->roleName());
        $roleName = preg_replace('/[^a-z0-9]+/u', '_', $roleName) ?? '';

        return trim($roleName, '_');
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        $roleKey = $this->roleKey();

        return $this->config->rolePermissions[$roleKey] ?? [];
    }

    public function can(string $permission): bool
    {
        $permission = trim($permission);

        if ($permission === '') {
            return false;
        }

        foreach ($this->permissions() as $grantedPermission) {
            if ($grantedPermission === '*') {
                return true;
            }

            if ($grantedPermission === $permission) {
                return true;
            }

            if (
                str_ends_with($grantedPermission, '.*')
                && str_starts_with(
                    $permission,
                    substr($grantedPermission, 0, -1)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $permissions
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $permissions
     */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }

        return true;
    }

    public function permissionLabel(string $permission): string
    {
        return $this->config->permissionLabels[$permission]
            ?? str_replace(['.', '_'], ' ', $permission);
    }
}
