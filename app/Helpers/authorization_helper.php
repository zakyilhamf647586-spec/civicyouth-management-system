<?php

use App\Libraries\Authorization;

if (!function_exists('authorization')) {
    function authorization(): Authorization
    {
        static $authorization = null;

        if (!$authorization instanceof Authorization) {
            $authorization = new Authorization();
        }

        return $authorization;
    }
}

if (!function_exists('auth_can')) {
    function auth_can(string $permission): bool
    {
        return authorization()->can($permission);
    }
}

if (!function_exists('auth_cannot')) {
    function auth_cannot(string $permission): bool
    {
        return !auth_can($permission);
    }
}

if (!function_exists('auth_can_any')) {
    /**
     * @param list<string> $permissions
     */
    function auth_can_any(array $permissions): bool
    {
        return authorization()->canAny($permissions);
    }
}

if (!function_exists('auth_role_name')) {
    function auth_role_name(): string
    {
        return authorization()->roleName();
    }
}
