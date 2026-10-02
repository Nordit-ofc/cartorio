<?php

namespace App\Support;

/**
 * Papéis fixos do sistema (Spatie roles) — ADR-006:
 *
 * - super_admin: sysadmin da nordit. Sem tenant; vê a plataforma inteira (propostas, instituições).
 * - tenant_admin: administrador de uma instituição cliente (cartório).
 * - tenant_user: usuário comum de uma instituição cliente.
 */
class Roles
{
    public const SUPER_ADMIN = 'super_admin';

    public const TENANT_ADMIN = 'tenant_admin';

    public const TENANT_USER = 'tenant_user';

    public const LABELS = [
        self::SUPER_ADMIN => 'Sysadmin',
        self::TENANT_ADMIN => 'Administrador da instituição',
        self::TENANT_USER => 'Usuário da instituição',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::LABELS);
    }
}
