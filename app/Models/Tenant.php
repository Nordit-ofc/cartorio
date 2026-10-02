<?php

namespace App\Models;

use App\Enums\InstitutionType;
use App\Traits\Auditable;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** Instituição cliente da nordit (cartório) — ADR-006. */
#[Fillable(['name', 'institution_type', 'cnpj', 'email', 'phone', 'city', 'state', 'active'])]
class Tenant extends Model
{
    use Auditable, HasFactory, HasPublicId, SoftDeletes;

    protected function casts(): array
    {
        return [
            'institution_type' => InstitutionType::class,
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenant): void {
            $tenant->slug ??= static::uniqueSlug($tenant->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'instituicao';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
