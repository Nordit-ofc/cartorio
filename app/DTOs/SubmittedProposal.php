<?php

namespace App\DTOs;

use App\Models\Proposal;

/** Resultado do envio: a proposta criada, ou a já existente em aberto (duplicidade). */
final readonly class SubmittedProposal
{
    public function __construct(
        public Proposal $proposal,
        public bool $isDuplicate,
    ) {}
}
