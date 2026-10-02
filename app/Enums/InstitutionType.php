<?php

namespace App\Enums;

/** A nordit atende só cartórios; o tipo fica para permitir outros no futuro, sem pergunta ao usuário. */
enum InstitutionType: string
{
    case Cartorio = 'cartorio';

    public function label(): string
    {
        return match ($this) {
            self::Cartorio => 'Cartório',
        };
    }
}
