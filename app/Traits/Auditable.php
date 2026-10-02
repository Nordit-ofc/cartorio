<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/** Preenche created_by/updated_by com o usuário autenticado (se houver). */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function ($model): void {
            if (Auth::id()) {
                $model->created_by ??= Auth::id();
                $model->updated_by ??= Auth::id();
            }
        });

        static::updating(function ($model): void {
            if (Auth::id()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
