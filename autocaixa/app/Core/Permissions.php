<?php
declare(strict_types=1);

namespace App\Core;

/** Permissões por perfil. A verificação é feita no backend em cada ação, nunca só na interface. */
final class Permissions
{
    private const MAP = [
        'admin'   => ['dashboard.view', 'products.manage', 'stock.adjust', 'sales.view', 'sales.cancel', 'reports.view', 'users.manage', 'audit.view'],
        'manager' => ['dashboard.view', 'products.manage', 'stock.adjust', 'sales.view', 'sales.cancel', 'reports.view'],
        'cashier' => ['dashboard.view', 'sales.view'],
    ];

    public static function allows(?string $role, string $permission): bool
    {
        return $role !== null && in_array($permission, self::MAP[$role] ?? [], true);
    }

    public static function roles(): array
    {
        return array_keys(self::MAP);
    }
}
