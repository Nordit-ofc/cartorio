<?php

namespace Tests\Concerns;

use App\Enums\InstitutionType;
use App\Models\InterestArea;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\InterestAreaSeeder;
use Database\Seeders\RoleSeeder;

/** Dados de base e usuários por papel, sem faker (resultados previsíveis). */
trait BuildsFixtures
{
    protected function seedBase(): void
    {
        $this->seed([RoleSeeder::class, InterestAreaSeeder::class]);
    }

    protected function makeSysadmin(array $attributes = []): User
    {
        $user = User::create($attributes + [
            'name' => 'Sysadmin',
            'email' => 'sysadmin@nordit.test',
            'password' => 'senha-forte-123',
            'active' => true,
        ]);

        return $user->assignRole(Roles::SUPER_ADMIN);
    }

    protected function makeTenantUser(string $role = Roles::TENANT_USER, array $tenantAttributes = []): User
    {
        $tenant = Tenant::create($tenantAttributes + [
            'name' => '1º Ofício de Notas de Recife',
            'institution_type' => InstitutionType::Cartorio,
            'active' => true,
        ]);

        $user = new User(['name' => 'Usuária do cartório', 'email' => 'cartorio@exemplo.test', 'password' => 'senha-forte-123', 'active' => true]);
        $user->tenant_id = $tenant->id;
        $user->save();

        return $user->assignRole($role);
    }

    /** @return array<string, mixed> */
    protected function validProposalPayload(array $overrides = []): array
    {
        return $overrides + [
            'contact_name' => 'Maria Souza',
            'organization' => '1º Ofício de Notas de Recife',
            'email' => 'Maria@Exemplo.com.br',
            'phone' => '(81) 99999-0000',
            'interest_areas' => InterestArea::query()->whereIn('slug', ['backup-continuidade', 'lgpd-conformidade'])->pluck('id')->all(),
            'message' => 'Queremos adequar o cartório ao Provimento 213.',
        ];
    }
}
