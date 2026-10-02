@extends('layouts.site')

@section('title', 'Consultar solicitação')

@section('content')
<section class="tracking site-container">
    @include('partials.page-header', [
        'title' => 'Consultar solicitação',
        'text' => 'Digite o número de protocolo recebido ao enviar sua solicitação de proposta.',
    ])

    <form class="tracking__card" method="GET" action="{{ route('proposals.tracking') }}" data-submit-once>
        <label class="tracking__label" for="protocolo">Número de protocolo</label>
        <div class="tracking__controls">
            <input id="protocolo" name="protocolo" type="text" value="{{ $protocol }}" maxlength="20" class="tracking__input" placeholder="NRD-2026-0001" required>
            <button type="submit" class="nd-btn nd-btn--primary nd-btn--lg" data-loading-text="Consultando…">Consultar</button>
        </div>
    </form>

    @if($proposal)
        @php($currentIndex = $reachedIndex)
        @php($reachedAt = $proposal->statusChanges->keyBy(fn ($change) => $change->status->value))
        <div class="tracking__result">
            <p class="tracking__protocol">{{ $proposal->protocol }}</p>
            <p class="tracking__detail"><strong>Solicitante:</strong> {{ $proposal->contact_name }} — {{ $proposal->organization }}</p>
            <p class="tracking__detail"><strong>Enviada em:</strong> {{ $proposal->created_at->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>

            {{-- Linha do tempo (antes StatusTimeline.vue) — agora com o status real, alterado pelo sysadmin. --}}
            <ol class="timeline">
                @foreach($steps as $index => $step)
                    <li @class([
                        'timeline__step',
                        'timeline__step--done' => $index <= $currentIndex,
                        'timeline__step--current' => $index === $currentIndex,
                    ])>
                        <span class="timeline__dot">
                            @if($index <= $currentIndex)<x-icon name="check" size="13" />@endif
                        </span>
                        <span class="timeline__label">{{ $step->label() }}</span>
                        @if($index <= $currentIndex && $reachedAt->has($step->value))
                            <span class="timeline__date">{{ $reachedAt[$step->value]->created_at->format('d/m') }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>

            @if($closing = $proposal->status->publicClosingMessage())
                <p class="tracking__closing">{{ $closing }}</p>
            @endif
        </div>
    @elseif($searched)
        <p class="tracking__not-found">Não encontramos nenhuma solicitação com esse protocolo. Confira o número e tente novamente.</p>
    @endif
</section>
@endsection
