<?php

namespace App\Repositories;

use App\Models\Proposal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/** Consultas de leitura das propostas (listagem do painel do sysadmin). */
class ProposalRepository
{
    public const PER_PAGE = 20;

    /**
     * @param  array{q?: ?string, status?: ?string, area?: ?string, from?: ?string, to?: ?string}  $filters
     */
    public function paginated(array $filters): LengthAwarePaginator
    {
        return Proposal::query()
            ->with('interestAreas')
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('protocol', 'like', "%{$term}%")
                    ->orWhere('contact_name', 'like', "%{$term}%")
                    ->orWhere('organization', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
            ))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['area'] ?? null, fn (Builder $query, string $area) => $query->whereHas(
                'interestAreas',
                fn (Builder $areas) => $areas->where('slug', $area)
            ))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /** @return array<string, int> total por status, para os cards do topo */
    public function countByStatus(): array
    {
        return Proposal::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}
