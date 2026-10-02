<?php

namespace App\Models;

use App\Enums\InstitutionType;
use App\Enums\ProposalSource;
use App\Enums\ProposalStatus;
use App\Traits\Auditable;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Solicitação de proposta vinda do site — formulário ou chat (SPEC-005/006). Global — sem tenant_id: é lead da nordit,
 * visível só ao super_admin. Criação e mudança de status passam só pelo ProposalService.
 */
#[Fillable(['contact_name', 'organization', 'institution_type', 'email', 'phone', 'message', 'source', 'ip_address', 'user_agent'])]
class Proposal extends Model
{
    use Auditable, HasPublicId, SoftDeletes;

    protected function casts(): array
    {
        return [
            'institution_type' => InstitutionType::class,
            'status' => ProposalStatus::class,
            'source' => ProposalSource::class,
        ];
    }

    public function interestAreas(): BelongsToMany
    {
        return $this->belongsToMany(InterestArea::class)->orderBy('sort_order');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(ProposalStatusChange::class)->oldest('created_at')->oldest('id');
    }

    /** Telefone só com dígitos no banco; formatado apenas na tela. */
    public function formattedPhone(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $pattern = strlen($this->phone) === 11 ? '/(\d{2})(\d{5})(\d{4})/' : '/(\d{2})(\d{4})(\d{4})/';

        return preg_replace($pattern, '($1) $2-$3', $this->phone);
    }
}
