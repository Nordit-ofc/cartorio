<?php

namespace Database\Seeders;

use App\Models\InterestArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * As 8 áreas de serviço do Provimento 213/2026 (antes em src/constants/services.js ·
 * SERVICE_MENU_ITEMS do projeto Vue). Idempotente: atualiza pelo slug.
 */
class InterestAreaSeeder extends Seeder
{
    public const AREAS = [
        ['icon' => 'servers', 'name' => 'Infraestrutura & Ambiente', 'description' => 'Energia, nobreak, ambiente'],
        ['icon' => 'network', 'name' => 'Redes & Conectividade', 'description' => 'Links, segmentação, VLAN'],
        ['icon' => 'shield-check', 'name' => 'Segurança da Informação', 'description' => 'MFA, criptografia, firewall'],
        ['icon' => 'cloud-backup', 'name' => 'Backup & Continuidade', 'description' => 'RPO/RTO, imutável, PCN/PRD'],
        ['icon' => 'monitor-chart', 'name' => 'Monitoramento & Auditoria', 'description' => 'Logs 5 anos, SIEM, testes'],
        ['icon' => 'lock', 'name' => 'LGPD & Conformidade', 'description' => 'DPO, políticas, ANPD'],
        ['icon' => 'link', 'name' => 'Integração & Interop', 'description' => 'CNJ e padrões abertos'],
        ['icon' => 'clipboard-check', 'name' => 'Governança de TI', 'description' => 'Inventário, dossiê técnico'],
    ];

    public function run(): void
    {
        foreach (self::AREAS as $order => $area) {
            InterestArea::updateOrCreate(
                ['slug' => Str::slug($area['name'])],
                $area + ['sort_order' => $order + 1, 'active' => true],
            );
        }
    }
}
