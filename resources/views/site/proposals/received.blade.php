@extends('layouts.site')

@section('title', 'Solicitação recebida')

@section('content')
<section class="proposal site-container">
    <div class="proposal__success">
        <span class="proposal__success-icon"><x-icon name="doc-check" size="30" /></span>
        <h1 class="proposal__success-title">Solicitação recebida!</h1>

        @if($protocol)
            <p class="proposal__protocol">{{ $protocol }}</p>
        @endif

        <p class="proposal__success-text">
            @if($duplicate)
                Já existe uma solicitação em aberto com este e-mail. Você pode acompanhar o andamento pelo protocolo acima.
            @else
                Guarde este número de protocolo — é com ele que você acompanha o andamento da sua proposta.
            @endif
        </p>

        <div class="proposal__success-actions">
            @if($protocol)
                <a href="{{ route('proposals.tracking', ['protocolo' => $protocol]) }}" class="nd-btn nd-btn--primary nd-btn--lg">Consultar solicitação</a>
            @endif
            <a href="{{ route('home') }}" class="nd-btn nd-btn--outline nd-btn--lg">Voltar ao início</a>
        </div>
    </div>
</section>
@endsection
