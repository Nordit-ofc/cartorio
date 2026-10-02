@extends('layouts.admin')

@section('title', 'Propostas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Solicitações de proposta</h1>
    <span class="text-muted small">{{ $proposals->total() }} {{ $proposals->total() === 1 ? 'resultado' : 'resultados' }}</span>
</div>

{{-- Totais por status; clicar filtra a lista. --}}
<div class="row g-3 mb-3">
    @foreach($statuses as $status)
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.proposals.index', ['status' => $status->value]) }}" class="card border-0 shadow-sm text-decoration-none h-100 @if(($filters['status'] ?? null) === $status->value) status-card--active @endif">
                <div class="card-body py-3">
                    <div class="small text-muted">{{ $status->label() }}</div>
                    <div class="fs-3 fw-bold text-body">{{ $countByStatus[$status->value] ?? 0 }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.proposals.index') }}" class="card border-0 shadow-sm mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label small" for="q">Busca</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" class="form-control" placeholder="Protocolo, nome, serventia ou e-mail">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label small" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">Todos</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label small" for="area">Área</label>
            <select id="area" name="area" class="form-select">
                <option value="">Todas</option>
                @foreach($interestAreas as $area)
                    <option value="{{ $area->slug }}" @selected(($filters['area'] ?? null) === $area->slug)>{{ $area->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-lg-1">
            <label class="form-label small" for="from">De</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
        </div>
        <div class="col-6 col-lg-1">
            <label class="form-label small" for="to">Até</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
        </div>
        <div class="col-12 col-lg-1 d-flex gap-2">
            <button class="btn btn-primary flex-fill" title="Filtrar"><i class="bi bi-funnel"></i></button>
            <a href="{{ route('admin.proposals.index') }}" class="btn btn-outline-secondary" title="Limpar filtros"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 proposals-table">
            <thead class="table-light">
                <tr>
                    <th>Enviada em</th>
                    <th>Protocolo</th>
                    <th>Nome</th>
                    <th>Serventia</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Áreas</th>
                    <th>Mensagem</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposals as $proposal)
                    <tr>
                        <td class="text-nowrap small">{{ $proposal->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.proposals.show', $proposal) }}" class="fw-semibold">{{ $proposal->protocol }}</a>
                            <div class="small text-muted">{{ $proposal->source->label() }}</div>
                        </td>
                        <td>{{ $proposal->contact_name }}</td>
                        <td>{{ $proposal->organization }}</td>
                        <td><a href="mailto:{{ $proposal->email }}">{{ $proposal->email }}</a></td>
                        <td class="text-nowrap">{{ $proposal->formattedPhone() ?? '—' }}</td>
                        <td class="small">{{ $proposal->interestAreas->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="small text-muted" title="{{ $proposal->message }}">{{ \Illuminate\Support\Str::limit($proposal->message ?? '—', 60) }}</td>
                        <td><span class="badge {{ $proposal->status->badge() }}">{{ $proposal->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">Nenhuma solicitação encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $proposals->links() }}</div>
@endsection
