<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Gera uma senha nova para um sysadmin. Enquanto não houver tela de "esqueci a senha", é o
 * caminho para recuperar o acesso (ex.: senha do seed perdida).
 */
#[Signature('sysadmin:reset-password {email? : E-mail do sysadmin (padrão: o do SYSADMIN_EMAIL)}')]
#[Description('Gera e exibe uma senha nova para um sysadmin')]
class ResetSysadminPassword extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower($this->argument('email') ?? config('nordit.sysadmin.email'));
        $user = User::query()->where('email', $email)->first();

        if (! $user?->hasRole(Roles::SUPER_ADMIN)) {
            $this->error("Nenhum sysadmin com o e-mail {$email}.");

            return self::FAILURE;
        }

        $password = Str::password(16, symbols: false);
        $user->forceFill(['password' => $password])->save();

        $this->info("Senha de {$email} redefinida.");
        $this->warn("Nova senha (anote agora, ela não será exibida de novo): {$password}");

        return self::SUCCESS;
    }
}
