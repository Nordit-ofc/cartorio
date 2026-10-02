<?php

namespace App\Enums;

/**
 * Estágios da solicitação de proposta (SPEC-005). Os três primeiros formam a linha do tempo
 * pública (SPEC-002 · RF-03); os demais encerram a solicitação e só o sysadmin os aplica.
 */
enum ProposalStatus: string
{
    case Received = 'received';
    case InReview = 'in_review';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Recebida',
            self::InReview => 'Em análise',
            self::ProposalSent => 'Proposta enviada',
            self::Won => 'Contratada',
            self::Lost => 'Não avançou',
            self::Archived => 'Arquivada',
        };
    }

    /** Classe de badge Bootstrap usada no painel do sysadmin. */
    public function badge(): string
    {
        return match ($this) {
            self::Received => 'text-bg-warning',
            self::InReview => 'text-bg-info',
            self::ProposalSent => 'text-bg-primary',
            self::Won => 'text-bg-success',
            self::Lost => 'text-bg-secondary',
            self::Archived => 'text-bg-light border',
        };
    }

    /** Em aberto = a equipe ainda não respondeu; usado na detecção de duplicidade (RF-08). */
    public function isOpen(): bool
    {
        return in_array($this, self::open(), true);
    }

    /** @return list<self> */
    public static function open(): array
    {
        return [self::Received, self::InReview];
    }

    /** @return list<self> etapas exibidas na linha do tempo da consulta pública */
    public static function publicSteps(): array
    {
        return [self::Received, self::InReview, self::ProposalSent];
    }

    /** Mensagem de encerramento na consulta pública; null enquanto a solicitação segue o fluxo. */
    public function publicClosingMessage(): ?string
    {
        return match ($this) {
            self::Won => 'Proposta aceita! Nossa equipe entrará em contato para iniciar a implantação.',
            self::Lost, self::Archived => 'Esta solicitação foi encerrada. Se quiser retomar a conversa, envie uma nova solicitação.',
            default => null,
        };
    }
}
