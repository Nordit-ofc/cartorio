<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Services\ProposalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** "Consultar solicitação" por protocolo (SPEC-002 · RF-03), com o status real do banco. */
class ProposalTrackingController extends Controller
{
    public function __invoke(Request $request, ProposalService $proposals): View
    {
        $protocol = mb_strtoupper(trim((string) $request->query('protocolo', '')));
        $proposal = $protocol !== '' && mb_strlen($protocol) <= 20 ? $proposals->findByProtocol($protocol) : null;
        $steps = ProposalStatus::publicSteps();

        // Etapa mais avançada da linha do tempo que a solicitação já atingiu, pelo histórico
        // (uma proposta arquivada antes do envio mostra só até onde chegou).
        $reachedIndex = $proposal?->statusChanges
            ->map(fn ($change) => array_search($change->status, $steps, true))
            ->filter(fn ($index) => $index !== false)
            ->max() ?? 0;

        return view('site.proposals.tracking', [
            'protocol' => $protocol,
            'searched' => $protocol !== '',
            'proposal' => $proposal,
            'steps' => $steps,
            'reachedIndex' => $reachedIndex,
        ]);
    }
}
