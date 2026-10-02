{{--
    Chat do site (SPEC-006; antes ChatWidget.vue). O HTML é só a casca: public/js/chat.js lê
    window.NORDIT_CHAT (config/chat.php via ChatWidgetComposer) e desenha mensagens e respostas.
--}}
<div id="chat-widget" class="chat-widget">
    <section class="chat-panel" id="chat-panel" aria-label="Chat de atendimento nordit" hidden>
        <header class="chat-panel__header">
            <div>
                <span class="chat-panel__brand">nord<span class="chat-panel__brand-accent">it</span></span>
                <span class="chat-panel__status">
                    <i class="chat-panel__status-dot" aria-hidden="true"></i>
                    {{ $chatConfig['header']['status'] }}
                </span>
            </div>
            <button class="chat-panel__close" type="button" aria-label="Fechar chat" data-chat-close>
                <x-icon name="close" size="18" />
            </button>
        </header>

        <div class="chat-panel__messages" data-chat-messages aria-live="polite"></div>

        <div class="chat-panel__controls" data-chat-controls></div>
    </section>

    <span class="chat-widget__label" data-chat-label>{{ $chatConfig['header']['bubble_label'] }}</span>
    <button class="chat-widget__bubble" type="button" aria-label="Abrir chat" aria-controls="chat-panel" aria-expanded="false" data-chat-toggle>
        <span data-chat-icon-open><x-icon name="chat" size="27" /></span>
        <span data-chat-icon-close hidden><x-icon name="close" size="24" /></span>
    </button>
</div>

<script>window.NORDIT_CHAT = @json($chatConfig);</script>
