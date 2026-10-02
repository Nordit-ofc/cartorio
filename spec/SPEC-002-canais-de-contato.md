# SPEC-002 — Canais de contato funcionais (protótipo)

- **Status:** Implementada (protótipo, incl. rev. 4)
- **Data:** 2026-07-09
- **Referências:**
  - [SPEC-001 — Landing page do site (protótipo)](SPEC-001-landing-page.md) (sistema visual e pontos de entrada: botões "Solicitar proposta", menu "Consultar solicitação", chat flutuante)
  - Design aprovado (direção 1b "Moderno & Acessível") para paleta, tipografia e componentes.

## Objetivo

Tornar funcionais, apenas com Vue (sem backend), os canais de contato previstos no design: solicitação de proposta com protocolo, consulta do status da solicitação e chat roteirizado. Toda a "API" é simulada no navegador por uma camada de serviço assíncrona, trocável por backend real no futuro sem alterar componentes.

A partir da **rev. 2**, cada solicitação também é entregue de verdade à nordit por e-mail, via serviço externo de formulário (RF-07) — o site permanece estático (hospedado na Vercel), sem backend próprio. Protocolo e consulta de status continuam simulados no navegador.

## Requisitos

### RF-01 — Formulário de solicitação de proposta

Nova rota `/solicitar-proposta`, acionada por todos os botões "Solicitar proposta" existentes (navbar, hero, faixa CTA) e pelo chat. Campos:

- Nome completo (obrigatório)
- Serventia (obrigatório)
- E-mail (obrigatório, formato validado)
- Telefone / WhatsApp (opcional)
- Áreas de interesse: seleção múltipla com as 8 áreas de serviço de `constants/services.js` (opcional)
- Mensagem (opcional)

Validação no cliente com mensagem de erro por campo; durante o envio simulado (latência artificial de ~800 ms) o botão fica desabilitado com estado "Enviando…".

### RF-02 — Protocolo e confirmação

Ao concluir o envio, o sistema gera um protocolo no formato `NRD-<ano>-<sequencial de 4 dígitos>` (ex.: `NRD-2026-0001`), persiste a solicitação com status inicial **Recebida** e exibe uma tela de sucesso com o protocolo em destaque, orientação para guardá-lo e link para "Consultar solicitação".

### RF-03 — Consulta de solicitação

Nova rota `/consultar-solicitacao`, acionada pelo item "Consultar solicitação" do menu Entrar e pelo botão de contorno da faixa CTA. Campo único para o protocolo; busca simulada exibe protocolo, data, solicitante (nome e serventia) e o status em uma linha do tempo de três estágios:

1. **Recebida** — imediato após o envio.
2. **Em análise** — automaticamente após 1 minuto (simulação para demonstração).
3. **Proposta enviada** — automaticamente após 5 minutos.

Protocolo inexistente exibe mensagem amigável de "solicitação não encontrada", sem quebrar o layout.

### RF-04 — Chat roteirizado

O balão flutuante existente (`ChatWidget`) passa a abrir/fechar um painel de chat ancorado ao canto inferior direito. Sem campo de digitação livre: a conversa avança por botões de resposta rápida.

- Cabeçalho em marinho com marca nordit e indicação "online".
- Mensagem de boas-vindas automática ao abrir pela primeira vez.
- Opções iniciais: **"Quero uma proposta"** (encaminha para `/solicitar-proposta`), **"Dúvidas sobre o Provimento 213"** (resposta curta explicando o provimento e oferta do diagnóstico gratuito), **"Acompanhar minha solicitação"** (encaminha para `/consultar-solicitacao`) e **"Falar no WhatsApp"** (RF-05).
- Respostas do "atendente" aparecem com breve indicador de "digitando…".
- Opção "↩ Voltar ao início" ao final de cada ramo reinicia as opções.
- O histórico da conversa persiste durante a sessão da aba (`sessionStorage`).

### RF-05 — Canal real: WhatsApp

Opção do chat que abre `https://wa.me/<numero>` em nova aba, com mensagem pré-preenchida ("Olá! Vim pelo site da nordit e quero saber mais."). A partir da **rev. 3**, o número oficial vem da variável de ambiente `VITE_WHATSAPP_NUMBER` (somente dígitos, com DDI — ex.: `5581999990000`), configurada no painel da Vercel; `src/constants/chat.js` a lê e mantém um placeholder como fallback para desenvolvimento local. Nenhum número real fica no repositório.

### RF-06 — Página "Disponível em breve" (plataforma)

Nova rota `/plataforma`, acionada pelo item "Acessar a plataforma" do menu Entrar. Página institucional simples, no sistema visual do site, com a mensagem **"Disponível em breve"**, breve texto sobre a futura área do cliente e botões para voltar ao início ou solicitar proposta. Sem login ou autenticação.

### RF-07 — Captura de leads por e-mail (rev. 2)

Ao concluir o envio do formulário (RF-01/RF-02), além da persistência local, o serviço envia os dados da solicitação — todos os campos, protocolo e data — por `POST` a um serviço externo de formulário (ex.: Web3Forms ou FormSubmit), que os entrega no e-mail da nordit.

- O endpoint (e a chave de acesso, se o serviço exigir) vem de variáveis de ambiente (`VITE_LEADS_ENDPOINT` e `VITE_LEADS_KEY`), configuradas no painel da Vercel — nenhum e-mail ou chave fica no repositório.
- **Envio não bloqueante:** falha do serviço externo não impede a geração do protocolo nem exibe erro ao visitante.
- Sem as variáveis configuradas (ex.: desenvolvimento local), o envio externo é ignorado e o comportamento atual se mantém integralmente.

### RNF-01 — Camada de serviço simulada

- Criação e consulta de solicitações passam exclusivamente por um serviço em `src/services/` com interface assíncrona (Promises), simulando latência de rede. A troca futura por API real deve exigir alteração apenas nesse serviço.
- Persistência das solicitações em `localStorage`; estado reativo em stores Pinia (`src/stores/`).
- A única requisição de rede é o `POST` de captura de lead (RF-07); a consulta de status permanece local e simulada.
- Roteiro do chat, textos do formulário e status ficam em `src/constants/`, nunca hardcoded nos componentes.

## Comportamentos

- Botões e links que apontavam para `#` nos pontos de entrada citados passam a navegar pelas rotas novas via Vue Router.
- Navegação entre rotas rola a página para o topo.
- As novas telas seguem o sistema visual da SPEC-001 (paleta Marinho/Teal, Poppins/DM Sans, botões-pílula, cantos arredondados, sombras suaves).

## Responsividade (breakpoint 900px)

- Formulário e consulta em coluna única no mobile, botões em largura total.
- Painel do chat ocupa a largura disponível (respeitando margens de 16 px) e altura máxima que preserve a navbar visível.

## Fora do escopo

- Backend/API próprios e status real de andamento (a consulta continua simulada; o e-mail do RF-07 é apenas captura do lead).
- E-mail automático de confirmação para o visitante.
- Login/autenticação real — "Acessar a plataforma" leva apenas à página "Disponível em breve" (RF-06).
- Campo de texto livre, IA ou atendimento humano no chat.
- Painel administrativo para visualizar solicitações recebidas.
- Integrações externas além do link `wa.me`.

## Critérios de aceitação

- [x] Formulário valida campos obrigatórios, simula envio e exibe protocolo `NRD-AAAA-NNNN` na confirmação.
- [x] Solicitação persiste após recarregar a página (localStorage).
- [x] Consulta por protocolo mostra a linha do tempo com o status correto pela regra de tempo; protocolo inexistente mostra mensagem amigável.
- [x] Chat abre/fecha pelo balão, percorre todos os ramos do roteiro e os encaminhamentos levam às rotas corretas; WhatsApp abre em nova aba.
- [x] Botões "Solicitar proposta" (navbar, hero, CTA) e "Consultar solicitação" (menu Entrar, CTA) navegam para as novas rotas.
- [x] "Acessar a plataforma" (menu Entrar) navega para a página "Disponível em breve".
- [x] Layout desktop e mobile coerente com o sistema visual aprovado.
- [x] `npm run build` sem erros.

Rev. 2 (RF-07):

- [x] Com `VITE_LEADS_ENDPOINT` configurada, o envio do formulário dispara o `POST` com todos os campos e o protocolo.
- [x] Falha ou indisponibilidade do serviço externo não impede a exibição do protocolo ao visitante.
- [x] Sem as variáveis de ambiente, o fluxo permanece o atual (sem requisições de rede).

## Revisões

- **rev. 1 (2026-07-09):** versão inicial — implementada.
- **rev. 2 (2026-07-09):** RF-07, captura de leads por e-mail via serviço externo de formulário (deploy na Vercel) — implementada.
- **rev. 3 (2026-07-13):** RF-05, número do WhatsApp passa a vir da variável de ambiente `VITE_WHATSAPP_NUMBER` (Vercel), com placeholder como fallback local — implementada.
- **rev. 4 (2026-07-13):** RF-04, o chat ganha a opção inicial "Ver dúvidas frequentes", que navega para `/duvidas` (SPEC-004 · RF-04) — implementada.
- **rev. 5 (2026-10-02):** a nordit passa a atender só cartórios: o formulário deixa de perguntar o tipo de instituição e "Serventia / órgão" vira "Serventia" (ver SPEC-005 rev. 3).
