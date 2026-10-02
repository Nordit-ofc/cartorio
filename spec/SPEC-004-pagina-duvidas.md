# SPEC-004 — Página "Dúvidas": FAQ e canal primário de atendimento

- **Status:** Implementada (protótipo, opção A — rev. 2)
- **Data:** 2026-07-13
- **Referências:**
  - [SPEC-002 — Canais de contato funcionais (protótipo)](SPEC-002-canais-de-contato.md) (camada de serviço simulada RNF-01, captura de leads RF-07 e protocolo RF-02, cujos padrões esta spec reaproveita)
  - [SPEC-003 — Página "Como funciona" (protótipo)](SPEC-003-pagina-como-funciona.md) (contexto de negócio: ecossistema com foco em cartórios)

## Objetivo

Transformar a aba "Dúvidas" no **canal primário de atendimento** do site: o visitante primeiro encontra respostas prontas (FAQ) e, se não resolver, registra a própria dúvida para ser respondida pela equipe nordit. Nesta fase o conteúdo do FAQ é temporário (mantido em `constants/`) e a "API" é simulada no navegador, como nas demais telas — mas a spec já define o contrato pensando na API real, pois as dúvidas precisam persistir de verdade.

## Requisitos

### RF-01 — FAQ temporário

Rota `/duvidas` (nome `faq`), ligada à aba "Dúvidas" da navbar. Lista de perguntas e respostas em acordeão (uma aberta por vez), agrupadas por tema:

- **Provimento 213/2026** — o que é, prazos, classes de serventia, consequências do descumprimento.
- **Serviços & plataforma** — o que o ecossistema cobre, como substitui múltiplos fornecedores, como acompanhar tudo pela plataforma.
- **Contratação** — diagnóstico gratuito, proposta, prazos de implantação. **Sem perguntas de preço/planos** (decisão 2 — a área de Planos foi removida do site nesta fase).

Conteúdo em `src/constants/faq.js`, com busca simples por texto (filtro client-side, opcional nesta fase — não incluída na rev. 2).

### RF-02 — Envio de dúvida pelo visitante

Abaixo do FAQ, seção "Não encontrou sua resposta?" com formulário: **nome, e-mail e dúvida** (todos obrigatórios, e-mail validado). Ao enviar:

1. A dúvida recebe um protocolo `DUV-<ano>-<sequencial>` (mesmo padrão do `NRD-` da SPEC-002 · RF-02).
2. É persistida localmente (`localStorage`, store Pinia) com status **Recebida**.
3. É entregue à equipe pelo canal de captura de leads (`VITE_LEADS_ENDPOINT`, não bloqueante), até existir API própria.
4. O visitante vê confirmação com o protocolo e orientação de que a resposta chega por e-mail.

### RF-03 — Consulta e ciclo de vida da dúvida

**A resposta em si é enviada ao visitante por e-mail, manualmente pela equipe** (decisão 3 — respondendo o lead recebido). A consulta por protocolo existe como acompanhamento de status, em seção própria no fim da página `/duvidas` (canal separado da consulta de propostas), reutilizando o padrão visual da SPEC-002 · RF-03 (componente comum `StatusTimeline`). Ciclo de vida: **Recebida → Em análise → Respondida** — no protótipo a transição é simulada por tempo (demonstrativa) e a tela orienta que a resposta chega por e-mail; na API real, o status muda quando a equipe responde.

### RF-04 — Integração com os demais canais

- **Dúvidas e chat permanecem canais separados** (decisão 4): o FAQ não abre dentro do painel do chat. O chat roteirizado (SPEC-002 · RF-04) apenas ganha a opção "Ver dúvidas frequentes", que navega para `/duvidas`.
- Dúvidas respondidas com frequência devem virar novas entradas do FAQ (processo editorial, fora do código).

## Persistência — decidido (2026-07-13)

**Decisão 1: opção A agora, com o contrato de serviço preparado para a C** — `src/services/faqService.js` concentra criação e consulta (interface assíncrona); quando a API própria existir, apenas esse arquivo muda (mesmo princípio da SPEC-002 · RNF-01). As opções avaliadas:

| Opção | Como funciona | Prós | Contras |
| --- | --- | --- | --- |
| **A. Somente lead por e-mail** (como hoje) | localStorage + POST ao serviço de formulário | Zero backend; já existe | Sem estado real; "Respondida" é simulação; sem histórico para a equipe |
| **B. Backend leve (BaaS)** | Supabase/Firebase gratuito: tabela `duvidas` + painel simples | Persistência real; status real; vira base do futuro módulo de atendimento da plataforma | Nova dependência externa; regras de acesso a definir |
| **C. API própria (futura plataforma)** | Endpoint `POST/GET /duvidas` no backend da nordit | Alinhado ao produto final | Backend ainda não existe; bloqueia a página até lá |

**B** fica como alternativa apenas se quisermos status real antes da plataforma.

## Fora do escopo

- Resposta automática por IA e atendimento em tempo real (o chat continua roteirizado).
- Painel administrativo para a equipe responder (depende da decisão de persistência).
- Notificação por e-mail ao visitante quando a dúvida for respondida (depende de backend).
- Votação/avaliação de utilidade das respostas do FAQ.

## Critérios de aceitação

- [x] Aba "Dúvidas" navega para `/duvidas` com FAQ em acordeão (uma pergunta aberta por vez) e conteúdo em `constants/faq.js`.
- [x] Formulário de dúvida valida os campos, gera protocolo `DUV-AAAA-NNNN` e entrega a dúvida à equipe pelo canal de leads (quando configurado).
- [x] Dúvida persiste (localStorage) e é consultável pelo protocolo, com o ciclo Recebida → Em análise → Respondida (simulado) e orientação de que a resposta chega por e-mail.
- [x] Chat oferece o atalho "Ver dúvidas frequentes" para `/duvidas` (canais separados).
- [x] Layout responsivo no sistema visual da SPEC-001; `npm run build` sem erros.

## Decisões da discussão (2026-07-13)

1. **Persistência:** opção A (lead por e-mail + localStorage), com o contrato do serviço preparado para a futura API própria (C).
2. **FAQ:** cobre os 3 temas propostos, **sem** perguntas de preço/planos.
3. **Resposta ao visitante:** por e-mail, manualmente (respondendo o lead); a consulta por protocolo mostra apenas o status.
4. **Canais:** dúvidas e chat permanecem separados; o chat apenas roteia para `/duvidas`.

## Revisões

- **rev. 1 (2026-07-13):** rascunho inicial para discussão.
- **rev. 2 (2026-07-13):** decisões 1–4 registradas e página implementada (opção A): rota `/duvidas`, FAQ em acordeão, envio com protocolo `DUV`, consulta de status na própria página e atalho no chat.
