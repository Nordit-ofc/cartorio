<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Model que pertence a uma instituição: aplica o TenantScope nas consultas e, na criação, copia
 * o tenant_id do usuário autenticado se o model ainda não tiver um (não sobrescreve um valor já
 * definido — é assim que o super_admin cria registros para outra instituição).
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $user = Auth::user();

            if ($user?->tenant_id && empty($model->tenant_id)) {
                $model->tenant_id = $user->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
