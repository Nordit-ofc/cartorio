@extends('layouts.site')

@section('content')
{{-- Raiz do site enquanto as páginas institucionais não são migradas (SPEC-005 rev. 2; visual do PlatformSoonView.vue). --}}
<section class="soon site-container">
    <div class="soon__card">
        <span class="nd-badge">Plataforma nordit</span>
        <h1 class="soon__title">Disponível em breve</h1>
        <p class="soon__text">
            Estamos construindo o novo site e a plataforma da nordit: TI completa para cartórios,
            em conformidade com o Provimento 213/2026 — tudo num só lugar.
        </p>
        <p class="soon__text">
            Enquanto isso, nosso assistente monta sua solicitação de proposta em um minuto.
            O diagnóstico é gratuito.
        </p>
        <div class="soon__actions">
            <button type="button" class="nd-btn nd-btn--primary nd-btn--lg" data-chat-open="proposal">
                <x-icon name="chat" size="20" /> Falar com o assistente
            </button>
            <a href="{{ route('proposals.create') }}" class="nd-btn nd-btn--outline nd-btn--lg">Prefiro preencher o formulário</a>
        </div>
        <p class="soon__track">
            Já enviou uma solicitação? <a href="{{ route('proposals.tracking') }}">Consulte pelo protocolo</a>.
        </p>
    </div>
</section>
@endsection
