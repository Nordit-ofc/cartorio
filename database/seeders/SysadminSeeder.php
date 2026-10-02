<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sysadmin inicial da plataforma (super_admin, sem instituição) — SPEC-005 · RF-09.
 *
 * E-mail: SYSADMIN_EMAIL ou o padrão sysadmin@nordit.com.br. Senha: SYSADMIN_PASSWORD ou, sem
 * ela, uma senha aleatória exibida uma única vez no terminal (nenhuma senha fica no repositório).
 * Idempotente: se a conta já existe, só garante o papel e nunca troca a senha — para isso use
 * `php artisan sysadmin:reset-password`.
 */
class SysadminSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('nordit.sysadmin');
        $email = mb_strtolower($config['email']);

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $password = filled($config['password']) ? $config['password'] : Str::password(16, symbols: false);

            $user = User::create([
                'name' => $config['name'],
                'email' => $email,
                'password' => $password,
                'active' => true,
            ]);

            $this->command?->info("Sysadmin criado: {$email}");

            if (blank($config['password'])) {
                $this->command?->warn("Senha gerada (anote agora, ela não será exibida de novo): {$password}");
            }
        }

        $user->assignRole(Roles::SUPER_ADMIN);
    }
}
