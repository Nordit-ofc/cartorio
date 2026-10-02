<?php

namespace App\Http\Controllers\Site;

use App\DTOs\ProposalDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProposalRequest;
use App\Models\InterestArea;
use App\Services\ProposalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** "Solicitar proposta" (SPEC-002 · RF-01/RF-02, persistência pela SPEC-005). */
class ProposalController extends Controller
{
    /** Campo-isca: invisível para pessoas; se vier preenchido, é robô. */
    public const HONEYPOT_FIELD = 'website';

    public function create(): View
    {
        return view('site.proposals.create', [
            'interestAreas' => InterestArea::query()->active()->get(),
        ]);
    }

    /**
     * Formulário (redireciona para a confirmação) e chat (SPEC-006: pede JSON e recebe o
     * protocolo; erros de validação voltam como 422 com as mesmas mensagens).
     */
    public function store(StoreProposalRequest $request, ProposalService $proposals): RedirectResponse|JsonResponse
    {
        // Robô recebe a mesma resposta de sucesso, sem gravar nada — não damos pista do filtro.
        if (filled($request->input(self::HONEYPOT_FIELD))) {
            return $this->submitted($request, protocol: null, duplicate: false);
        }

        $result = $proposals->submit(
            ProposalDTO::fromValidated($request->validated(), $request->ip(), $request->userAgent())
        );

        return $this->submitted($request, $result->proposal->protocol, $result->isDuplicate);
    }

    private function submitted(Request $request, ?string $protocol, bool $duplicate): RedirectResponse|JsonResponse
    {
        $payload = ['protocol' => $protocol, 'duplicate' => $duplicate];

        if ($request->expectsJson()) {
            return response()->json($payload + [
                'tracking_url' => $protocol ? route('proposals.tracking', ['protocolo' => $protocol]) : null,
            ], $duplicate ? 200 : 201);
        }

        return redirect()->route('proposals.received')->with('submitted', $payload);
    }

    /** Tela de confirmação; só existe logo após um envio (dado vem da sessão). */
    public function received(): View|RedirectResponse
    {
        $submitted = session('submitted');

        if (! $submitted) {
            return redirect()->route('proposals.create');
        }

        return view('site.proposals.received', $submitted);
    }
}
