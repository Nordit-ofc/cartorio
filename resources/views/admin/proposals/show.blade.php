@extends('layouts.admin')

@section('title', $proposal->protocol)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.proposals.index') }}">Propostas</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $proposal->protocol }}</li>
    </ol>
</nav>

<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <h1 class="h3 mb-0">{{ $proposal->protocol }}</h1>
    <span class="badge fs-6 {{ $proposal->status->badge() }}">{{ $proposal->status->label() }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Dados da solicitação</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $proposal->contact_name }}</dd>
                    <dt class="col-sm-4">Serventia</dt><dd class="col-sm-8">{{ $proposal->organization }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><a href="mailto:{{ $proposal->email }}">{{ $proposal->email }}</a></dd>
                    <dt class="col-sm-4">Telefone</dt>
                    <dd class="col-sm-8">
                        @if($proposal->phone)
                            {{ $proposal->formattedPhone() }}
                            <a href="https://wa.me/55{{ $proposal->phone }}" target="_blank" rel="noopener" class="ms-2 small"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                        @else
                            —
                        @endif
                    </dd>
                    <dt class="col-sm-4">Áreas de interesse</dt>
                    <dd class="col-sm-8">
                        @forelse($proposal->interestAreas as $area)
                            <span class="badge rounded-pill text-bg-light border">{{ $area->name }}</span>
                        @empty
                            —
                        @endforelse
                    </dd>
                    <dt class="col-sm-4">Mensagem</dt><dd class="col-sm-8">{!! nl2br(e($proposal->message ?? '—')) !!}</dd>
                    <dt class="col-sm-4">Enviada em</dt><dd class="col-sm-8">{{ $proposal->created_at->format('d/m/Y H:i') }}</dd>
                    <dt class="col-sm-4">Canal</dt><dd class="col-sm-8">{{ $proposal->source->label() }}</dd>
                    <dt class="col-sm-4 text-muted small">Origem</dt><dd class="col-sm-8 text-muted small">IP {{ $proposal->ip_address ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5 mb-3">Alterar status</h2>
                <form method="POST" action="{{ route('admin.proposals.status', $proposal) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-2">
                        <label class="form-label small" for="status">Novo status</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $proposal->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="note">Observação interna <span class="text-muted">(opcional)</span></label>
                        <textarea id="note" name="note" rows="3" maxlength="1000" class="form-control" placeholder="Ex.: proposta enviada por e-mail em 30/09">{{ old('note') }}</textarea>
                    </div>
                    <button class="btn btn-primary w-100">Salvar</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Histórico</h2>
                <ul class="list-unstyled mb-0 status-history">
                    @foreach($proposal->statusChanges->reverse() as $change)
                        <li>
                            <span class="badge {{ $change->status->badge() }}">{{ $change->status->label() }}</span>
                            <span class="small text-muted ms-1">{{ $change->created_at->format('d/m/Y H:i') }} · {{ $change->user?->name ?? 'Formulário do site' }}</span>
                            @if($change->note)<div class="small mt-1">{{ $change->note }}</div>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
