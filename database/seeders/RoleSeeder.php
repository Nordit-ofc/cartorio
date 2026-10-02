<?php

namespace Database\Seeders;

use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Os 3 papéis fixos (ADR-006). Idempotente: pode rodar em todo deploy. */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Roles::all() as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
