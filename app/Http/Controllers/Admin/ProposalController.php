<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProposalStatusRequest;
use App\Models\InterestArea;
use App\Models\Proposal;
use App\Repositories\ProposalRepository;
use App\Services\ProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Painel do sysadmin: análise das solicitações de proposta (SPEC-005 · RF-10/RF-11). */
class ProposalController extends Controller
{
    public function index(Request $request, ProposalRepository $repository): View
    {
        Gate::authorize('viewAny', Proposal::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:20'],
            'area' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return view('admin.proposals.index', [
            'proposals' => $repository->paginated($filters),
            'countByStatus' => $repository->countByStatus(),
            'filters' => $filters,
            'statuses' => ProposalStatus::cases(),
            'interestAreas' => InterestArea::query()->active()->get(),
        ]);
    }

    public function show(Proposal $proposal): View
    {
        Gate::authorize('view', $proposal);

        $proposal->load(['interestAreas', 'statusChanges.user']);

        return view('admin.proposals.show', [
            'proposal' => $proposal,
            'statuses' => ProposalStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateProposalStatusRequest $request, Proposal $proposal, ProposalService $proposals): RedirectResponse
    {
        $status = ProposalStatus::from($request->validated('status'));

        $proposals->changeStatus($proposal, $status, $request->validated('note'), $request->user());

        return redirect()
            ->route('admin.proposals.show', $proposal)
            ->with('success', "Status alterado para \"{$status->label()}\".");
    }
}
