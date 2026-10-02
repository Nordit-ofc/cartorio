<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seed de produção: só dados de base, todos idempotentes. Sem WithoutModelEvents — os models
 * dependem de eventos de criação (public_id, created_by).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            InterestAreaSeeder::class,
            SysadminSeeder::class,
        ]);
    }
}
