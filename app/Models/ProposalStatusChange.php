<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Entrada do histórico de status de uma proposta (só created_at; nunca é editada). */
#[Fillable(['status', 'note', 'user_id'])]
class ProposalStatusChange extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['status' => ProposalStatus::class];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
