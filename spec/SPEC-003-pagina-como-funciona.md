# SPEC-003 — Página "Como funciona" (protótipo)

- **Status:** Implementada (protótipo)
- **Data:** 2026-07-13
- **Referências:**
  - [SPEC-001 — Landing page do site (protótipo)](SPEC-001-landing-page.md) (sistema visual e navbar; rev. 2 remove "Planos" e liga "Como funciona" a esta página)
  - [SPEC-002 — Canais de contato funcionais (protótipo)](SPEC-002-canais-de-contato.md) (captura de leads RF-07 e WhatsApp RF-05, reutilizados aqui)
  - Referência de layout do passo a passo numerado: site da Contabilizei (números grandes em destaque).

## Contexto de negócio

A TI de cartórios costuma ser fragmentada entre vários fornecedores (backup com uma empresa, LGPD com outra, redes com uma terceira) ou concentrada em um prestador único pouco profissionalizado. A nordit se posiciona como um **ecossistema**: concentra esses serviços numa única plataforma, eliminando a contratação de múltiplos fornecedores. A porta de entrada e o foco comercial atual são os **cartórios**, fidelizados pela conformidade com o Provimento 213/2026 do CNJ.

## Objetivo

Página institucional acessível pela aba "Como funciona" da navbar, apresentando quem é a nordit, como o serviço funciona na prática (passo a passo numerado), os serviços do ecossistema e um canal direto com um especialista (formulário + WhatsApp).

## Requisitos

### RF-01 — Quem somos

Seção de abertura em duas colunas: texto institucional (o problema da TI fragmentada e a proposta de ecossistema único em conformidade com o Provimento 213/2026) e imagem real (`src/assets/images/especialista-ti.jpg`).

### RF-02 — Passo a passo numerado

Seção "Do diagnóstico à conformidade em 4 passos", com cards numerados em destaque (números grandes em teal, no estilo do passo a passo da Contabilizei):

1. **Diagnóstico gratuito** — avaliação da serventia frente ao Provimento 213/2026.
2. **Plano sob medida** — proposta com escopo, prazos e investimento por classe.
3. **Implantação assistida** — implantação sem interromper o atendimento.
4. **Operação contínua** — monitoramento, suporte e dossiê técnico pela plataforma.

### RF-03 — Serviços em destaque

Grade de cards compactos com ícone, título e descrição curta das 8 áreas de serviço (reutiliza `SERVICE_MENU_ITEMS` de `constants/services.js`).

### RF-04 — Fale com um especialista

Faixa em marinho no fim da página com duas ações:

- **"Falar com um especialista"** — abre, na própria seção, um formulário com os campos **nome, e-mail, número de telefone e cidade** (todos obrigatórios, e-mail validado). O envio reutiliza o canal de captura de leads da SPEC-002 · RF-07 (`VITE_LEADS_ENDPOINT`/`VITE_LEADS_KEY`, não bloqueante); ao concluir, exibe mensagem de sucesso no lugar do formulário.
- **"Chamar no WhatsApp"** — botão verde (cor institucional do WhatsApp, com ícone) que abre `wa.me` em nova aba com mensagem pré-preenchida, usando o número de `VITE_WHATSAPP_NUMBER` (SPEC-002 · RF-05 rev. 3).

### RNF-01 — Conteúdo e estrutura

- Rota `/como-funciona` (nome `how-it-works`), acessível pela navbar (desktop e mobile) e pelo botão "Como funciona" do hero da Home.
- Todos os textos em `src/constants/howItWorks.js`; nada hardcoded nos componentes.
- Seções em `src/views/how-it-works/components/` (About, Process, ServicesHighlight, Specialist), seguindo o sistema visual da SPEC-001.

## Responsividade (breakpoint 900px)

- Quem somos: coluna única, imagem abaixo do texto.
- Passo a passo: cards empilhados com o número à esquerda.
- Serviços: grade 2 colunas.
- Especialista: botões empilhados em largura total; formulário em coluna única.

## Fora do escopo

- Página "Planos" (removida da navbar nesta fase; será planejada depois).
- Página "Dúvidas" (tratada na SPEC-004, em discussão).
- Agendamento real, CRM ou fila de atendimento — o contato é captura de lead por e-mail.

## Critérios de aceitação

- [x] Navbar (desktop e mobile) e botão do hero navegam para `/como-funciona`.
- [x] Seções Quem somos, passo a passo (4 números em destaque), serviços com ícones e especialista presentes e no sistema visual do site.
- [x] Formulário valida os 4 campos, envia via canal de leads (quando configurado) e exibe confirmação; falha externa não quebra o fluxo.
- [x] Botão do WhatsApp abre `wa.me/<VITE_WHATSAPP_NUMBER>` em nova aba com mensagem pré-preenchida.
- [x] Layout responsivo no breakpoint 900px.
- [x] `npm run build` sem erros.

## Revisões

- **rev. 1 (2026-07-13):** versão inicial — implementada.
- **rev. 2 (2026-10-02):** a nordit passa a atender só cartórios; contexto sem "prefeituras".
