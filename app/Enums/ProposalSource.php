<?php

namespace App\Enums;

/** Por onde a solicitação chegou: formulário ou chat (SPEC-006). */
enum ProposalSource: string
{
    case Form = 'form';
    case Chat = 'chat';

    public function label(): string
    {
        return match ($this) {
            self::Form => 'Formulário',
            self::Chat => 'Chat',
        };
    }
}
