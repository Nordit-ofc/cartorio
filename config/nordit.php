<?php

/*
 * Configurações próprias da nordit. Valores reais só no .env do servidor — nenhum e-mail,
 * número ou senha fica no repositório.
 */
return [

    // Quem recebe o aviso de nova solicitação de proposta (RF-07). Vazio = não envia.
    'leads_mail_to' => env('NORDIT_LEADS_MAIL_TO'),

    // WhatsApp oficial, só dígitos com DDI (SPEC-002 · RF-05).
    'whatsapp_number' => env('NORDIT_WHATSAPP_NUMBER', '5500000000000'),

    // Sysadmin inicial, criado pelo SysadminSeeder. Sem SYSADMIN_PASSWORD, a senha é gerada e
    // exibida uma única vez no terminal do seed.
    'sysadmin' => [
        'name' => env('SYSADMIN_NAME', 'Sysadmin nordit'),
        'email' => env('SYSADMIN_EMAIL', 'sysadmin@nordit.com.br'),
        'password' => env('SYSADMIN_PASSWORD'),
    ],

];
