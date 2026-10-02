<?php

/*
 * Menus do site público (antes em src/constants/services.js do projeto Vue). Itens com rota
 * ainda não migrada ficam ocultos automaticamente (Route::has) até a página existir.
 */
return [

    'nav_links' => [
        ['label' => 'Como funciona', 'route' => 'how-it-works'],
        ['label' => 'Dúvidas', 'route' => 'faq'],
    ],

    'login_menu' => [
        ['icon' => 'login', 'title' => 'Acessar a plataforma', 'description' => 'Login de clientes', 'emphasis' => true, 'route' => 'login'],
        ['icon' => 'doc-check', 'title' => 'Consultar solicitação', 'description' => 'Acompanhe sua proposta', 'emphasis' => false, 'route' => 'proposals.tracking'],
    ],

];
