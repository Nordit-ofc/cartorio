<?php

/*
 * Roteiro do chat do site (SPEC-006; antes src/constants/chat.js). Sem IA por enquanto: o
 * atendente segue este roteiro e, no fluxo "Quero uma proposta", faz as perguntas do formulário
 * uma a uma e envia a solicitação para o mesmo backend (POST /solicitar-proposta, JSON).
 *
 * Opções: 'next' vai para outro passo; 'action' => 'proposal' inicia o fluxo de perguntas;
 * 'route' abre uma página; 'action' => 'whatsapp' abre o WhatsApp oficial em nova aba.
 * Textos aceitam {variavel} com as respostas já dadas (ex.: {first_name}, {protocol}).
 */

$backToStart = ['label' => '↩ Voltar ao início', 'next' => 'menu'];

$startOptions = [
    ['label' => 'Quero uma proposta', 'action' => 'proposal'],
    ['label' => 'Dúvidas sobre o Provimento 213', 'next' => 'provimento'],
    ['label' => 'Acompanhar minha solicitação', 'next' => 'acompanhar'],
    ['label' => 'Falar no WhatsApp', 'action' => 'whatsapp', 'next' => 'whatsapp'],
];

return [

    'typing_delay_ms' => 650,

    'header' => [
        'status' => 'online agora',
        'bubble_label' => 'Fale com a gente 👋',
    ],

    'whatsapp_message' => 'Olá! Vim pelo site da nordit e quero saber mais.',

    'steps' => [
        'inicio' => [
            'messages' => ['Olá! 👋 Bem-vindo à nordit.', 'Como podemos ajudar você hoje?'],
            'options' => $startOptions,
        ],
        'menu' => [
            'messages' => ['Posso ajudar em mais alguma coisa?'],
            'options' => $startOptions,
        ],
        'provimento' => [
            'messages' => [
                'O Provimento 213/2026 do CNJ define os requisitos mínimos de TI para as serventias — são 8 áreas, de backup e segurança a LGPD e governança.',
                'A nordit cuida de todas elas por você, a começar por um diagnóstico gratuito.',
            ],
            'options' => [
                ['label' => 'Quero o diagnóstico gratuito', 'action' => 'proposal'],
                $backToStart,
            ],
        ],
        'acompanhar' => [
            'messages' => ['Claro! Digite o número de protocolo (ex.: NRD-2026-0001) que eu abro o acompanhamento.'],
            'input' => ['type' => 'protocol', 'placeholder' => 'NRD-2026-0001', 'max' => 20],
            'options' => [$backToStart],
        ],
        'whatsapp' => [
            'messages' => ['Boa! Abrimos o WhatsApp em uma nova aba — te esperamos lá. 😉'],
            'options' => [$backToStart],
        ],
    ],

    // Fluxo "Quero uma proposta": as mesmas perguntas e regras do formulário (StoreProposalRequest).
    'proposal' => [
        'intro' => [
            'Ótimo! Vou montar sua solicitação com algumas perguntas rápidas — leva cerca de 1 minuto.',
            'O diagnóstico é gratuito e retornamos com uma proposta sob medida.',
        ],
        'questions' => [
            ['field' => 'contact_name', 'type' => 'text', 'label' => 'Nome', 'ask' => 'Para começar, qual é o seu nome completo?', 'placeholder' => 'Seu nome', 'max' => 120],
            ['field' => 'organization', 'type' => 'text', 'label' => 'Serventia', 'ask' => 'Prazer, {first_name}! Qual é a serventia?', 'placeholder' => 'Ex.: 1º Ofício de Notas de Recife', 'max' => 160],
            ['field' => 'email', 'type' => 'email', 'label' => 'E-mail', 'ask' => 'Qual e-mail podemos usar para enviar a proposta?', 'placeholder' => 'voce@exemplo.com.br', 'max' => 160],
            ['field' => 'phone', 'type' => 'tel', 'label' => 'Telefone', 'ask' => 'Tem um telefone ou WhatsApp para contato? (opcional)', 'placeholder' => '(00) 00000-0000', 'max' => 20, 'optional' => true],
            ['field' => 'interest_areas', 'type' => 'multi', 'label' => 'Áreas', 'ask' => 'Quais áreas mais interessam? Toque em quantas quiser e depois em "Concluir".', 'optional' => true],
            ['field' => 'message', 'type' => 'textarea', 'label' => 'Mensagem', 'ask' => 'Quer contar um pouco do seu cenário atual? (opcional)', 'placeholder' => 'Sua mensagem', 'max' => 2000, 'optional' => true],
        ],
        'invalid' => [
            'required' => 'Preciso dessa informação para continuar. 🙂',
            'email' => 'Hmm, esse e-mail não parece válido. Pode conferir?',
            'phone' => 'Informe o telefone com DDD, ex.: (81) 99999-0000 — ou toque em "Pular".',
            'max' => 'Ficou um pouco longo. Pode resumir?',
        ],
        'skip_label' => 'Pular',
        'done_label' => 'Concluir',
        'none_label' => 'Nenhuma',
        'summary_intro' => 'Confira o resumo da sua solicitação:',
        'confirm_label' => 'Enviar solicitação',
        'restart_label' => 'Corrigir (recomeçar)',
        'form_label' => 'Prefiro preencher o formulário',
        'sending' => 'Enviando sua solicitação…',
        'success' => [
            'Solicitação enviada! ✅',
            'Seu protocolo é {protocol}. Guarde-o para acompanhar o andamento — também avisamos nossa equipe por e-mail.',
        ],
        'duplicate' => [
            'Encontrei uma solicitação em aberto com este e-mail: {protocol}.',
            'Nossa equipe já está cuidando dela — você pode acompanhar pelo protocolo.',
        ],
        'track_label' => 'Acompanhar solicitação',
        'errors' => [
            'expired' => 'Sua sessão expirou. Recarregue a página e tente de novo.',
            'throttled' => 'Muitas tentativas em pouco tempo. Aguarde um minuto e tente de novo.',
            'generic' => 'Não consegui enviar agora. Tente de novo em instantes ou use o formulário.',
        ],
    ],

];
