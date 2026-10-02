# Especificações (Spec-Driven Development)

Esta pasta contém as especificações do sistema. O projeto segue Spec-Driven Development:

1. Toda funcionalidade nasce como uma especificação nesta pasta, **antes** da implementação.
2. A spec é aprovada pelo responsável do projeto antes de virar código.
3. A implementação segue a spec; qualquer desvio necessário exige atualizar a spec primeiro.
4. Decisões de arquitetura (stack, estrutura, padrões) não ficam aqui — são registradas em `docs/adr/`.

## Formato

- **Nome do arquivo:** `SPEC-NNN-slug.md` (numeração sequencial com três dígitos).
- **Cabeçalho:** Status (`Rascunho` | `Aprovada` | `Implementada` | `Obsoleta`), Data e Referências.
- **Seções:** Objetivo, Requisitos, Comportamentos, Responsividade (quando aplicável), Fora do escopo e Critérios de aceitação.

## Índice

- [SPEC-001 — Landing page do site (protótipo)](SPEC-001-landing-page.md)
- [SPEC-002 — Canais de contato funcionais (protótipo)](SPEC-002-canais-de-contato.md)
- [SPEC-003 — Página "Como funciona" (protótipo)](SPEC-003-pagina-como-funciona.md)
- [SPEC-004 — Página "Dúvidas": FAQ e canal primário de atendimento](SPEC-004-pagina-duvidas.md)
- [SPEC-005 — Propostas persistidas e painel do sysadmin](SPEC-005-propostas-persistidas-e-painel-sysadmin.md) — primeira spec do monolito Laravel (ADR-005)
- [SPEC-006 — Chat que monta a solicitação de proposta](SPEC-006-chat-que-monta-a-solicitacao.md)

> As SPEC-001 a 004 foram escritas para o protótipo Vue. O conteúdo (textos, fluxos, visual) continua valendo; onde falam de Vue Router, Pinia, `localStorage` ou variáveis `VITE_*`, leia como a implementação equivalente em Laravel (ADR-005).
