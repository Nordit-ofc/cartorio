@extends('layouts.site')

@section('title', 'Solicitar proposta')

@section('content')
<section class="proposal site-container">
    @include('partials.page-header', [
        'title' => 'Solicitar proposta',
        'text' => 'Conte um pouco sobre sua serventia. O diagnóstico é gratuito e retornamos com uma proposta sob medida.',
    ])

    <form class="proposal__card" method="POST" action="{{ route('proposals.store') }}" novalidate data-submit-once>
        @csrf

        {{-- Campo-isca contra robôs: escondido de pessoas e de leitores de tela. --}}
        <div class="nd-honeypot" aria-hidden="true">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="{{ \App\Http\Controllers\Site\ProposalController::HONEYPOT_FIELD }}" tabindex="-1" autocomplete="off">
        </div>

        <div class="proposal__grid">
            @foreach([
                ['name' => 'contact_name', 'label' => 'Nome completo', 'type' => 'text', 'placeholder' => 'Seu nome', 'max' => 120, 'autocomplete' => 'name'],
                ['name' => 'organization', 'label' => 'Serventia', 'type' => 'text', 'placeholder' => 'Ex.: 1º Ofício de Notas de Recife', 'max' => 160, 'autocomplete' => 'organization'],
                ['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'placeholder' => 'voce@exemplo.com.br', 'max' => 160, 'autocomplete' => 'email'],
                ['name' => 'phone', 'label' => 'Telefone / WhatsApp', 'type' => 'tel', 'placeholder' => '(00) 00000-0000', 'max' => 20, 'autocomplete' => 'tel', 'optional' => true],
            ] as $field)
                <div class="form-field">
                    <label class="form-field__label" for="{{ $field['name'] }}">
                        {{ $field['label'] }}
                        @if($field['optional'] ?? false)<em class="form-field__optional">(opcional)</em>@endif
                    </label>
                    <input
                        id="{{ $field['name'] }}"
                        name="{{ $field['name'] }}"
                        type="{{ $field['type'] }}"
                        value="{{ old($field['name']) }}"
                        placeholder="{{ $field['placeholder'] }}"
                        maxlength="{{ $field['max'] }}"
                        autocomplete="{{ $field['autocomplete'] }}"
                        @if($field['name'] === 'phone') data-mask="phone" inputmode="numeric" @endif
                        @unless($field['optional'] ?? false) required @endunless
                        class="form-field__input @error($field['name']) form-field__input--error @enderror"
                        @error($field['name']) aria-invalid="true" aria-describedby="{{ $field['name'] }}-error" @enderror
                    >
                    @error($field['name'])
                        <span id="{{ $field['name'] }}-error" class="form-field__error">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        </div>

        <fieldset class="form-group">
            <legend class="form-field__label">
                Áreas de interesse
                <em class="form-field__optional">(opcional — selecione quantas quiser)</em>
            </legend>
            <div class="pill-options">
                @foreach($interestAreas as $area)
                    <label class="pill-option pill-option--sm">
                        <input type="checkbox" name="interest_areas[]" value="{{ $area->id }}" class="visually-hidden" @checked(in_array((string) $area->id, (array) old('interest_areas', []), true))>
                        {{ $area->name }}
                    </label>
                @endforeach
            </div>
            @error('interest_areas.*')
                <span class="form-field__error">{{ $message }}</span>
            @enderror
        </fieldset>

        <div class="form-field form-group">
            <label class="form-field__label" for="message">Mensagem <em class="form-field__optional">(opcional)</em></label>
            <textarea id="message" name="message" rows="4" maxlength="2000" class="form-field__input form-field__textarea @error('message') form-field__input--error @enderror" placeholder="Conte um pouco do seu cenário atual (opcional)">{{ old('message') }}</textarea>
            @error('message')
                <span class="form-field__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="nd-btn nd-btn--primary nd-btn--lg nd-btn--block" data-loading-text="Enviando…">
                Enviar solicitação
            </button>
        </div>
    </form>
</section>
@endsection
