<?php

namespace App\Scopes;

use App\Support\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Filtra por tenant_id quando o usuário autenticado pertence a uma instituição. O super_admin
 * (sem tenant) nunca é filtrado. Atua só em SELECT; inserts não são afetados.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $guard = Auth::guard();

        // Durante o carregamento da sessão, chamar Auth::user() aqui buscaria o próprio User
        // de novo, reaplicando este scope recursivamente. Não remova esta checagem.
        if (! $guard->hasUser()) {
            return;
        }

        $user = $guard->user();

        if ($user?->tenant_id && ! $user->hasRole(Roles::SUPER_ADMIN)) {
            $builder->where($model->getTable().'.tenant_id', $user->tenant_id);
        }
    }
}
