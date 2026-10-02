<?php

namespace App\Services;

use App\DTOs\ProposalDTO;
use App\DTOs\SubmittedProposal;
use App\Enums\ProposalStatus;
use App\Mail\NewProposalReceived;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Regras da solicitação de proposta (SPEC-005). Único ponto que cria ou muda o status. */
class ProposalService
{
    public const PROTOCOL_PREFIX = 'NRD';

    public function __construct(private readonly ProtocolGenerator $protocols) {}

    /**
     * Grava a solicitação com status "Recebida" e avisa a nordit por e-mail (RF-07). Se o mesmo
     * e-mail já tem uma solicitação em aberto, devolve essa em vez de criar outra (RF-08).
     */
    public function submit(ProposalDTO $dto): SubmittedProposal
    {
        $existing = $this->findOpenDuplicate($dto);

        if ($existing) {
            return new SubmittedProposal($existing, isDuplicate: true);
        }

        $proposal = DB::transaction(function () use ($dto): Proposal {
            $proposal = new Proposal($dto->attributes());
            $proposal->protocol = $this->protocols->next(self::PROTOCOL_PREFIX);
            $proposal->status = ProposalStatus::Received;
            $proposal->save();

            $proposal->interestAreas()->sync($dto->interestAreaIds);
            $proposal->statusChanges()->create(['status' => ProposalStatus::Received]);

            return $proposal;
        });

        $this->notifyTeam($proposal);

        return new SubmittedProposal($proposal, isDuplicate: false);
    }

    /** Sysadmin avança (ou corrige) o status; cada mudança entra no histórico com a observação. */
    public function changeStatus(Proposal $proposal, ProposalStatus $status, ?string $note, User $user): void
    {
        DB::transaction(function () use ($proposal, $status, $note, $user): void {
            $proposal->status = $status;
            $proposal->save();

            $proposal->statusChanges()->create([
                'status' => $status,
                'note' => $note,
                'user_id' => $user->id,
            ]);
        });
    }

    public function findByProtocol(string $protocol): ?Proposal
    {
        return Proposal::query()
            ->with(['statusChanges', 'interestAreas'])
            ->where('protocol', mb_strtoupper(trim($protocol)))
            ->first();
    }

    private function findOpenDuplicate(ProposalDTO $dto): ?Proposal
    {
        return Proposal::query()
            ->where('email', $dto->email)
            ->whereIn('status', ProposalStatus::open())
            ->latest()
            ->first();
    }

    /** Não bloqueante: falha no e-mail nunca impede o visitante de receber o protocolo. */
    private function notifyTeam(Proposal $proposal): void
    {
        $recipient = config('nordit.leads_mail_to');

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(new NewProposalReceived($proposal));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
