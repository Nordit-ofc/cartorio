<?php

namespace App\Traits;

use Illuminate\Support\Str;

/** Gera um ULID em public_id; as URLs usam esse campo e nunca expõem o id incremental. */
trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
