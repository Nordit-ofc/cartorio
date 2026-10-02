# ADR-001 — Adoção de Spec-Driven Development

- **Status:** Aceito
- **Data:** 2026-07-08

## Contexto

O projeto nasce de um material normativo extenso (Provimento 213/2026 do CNJ) e de um design aprovado externamente. Sem um processo formal, o risco de a implementação divergir do que foi definido — ou de decisões ficarem apenas na cabeça de quem participou — é alto.

## Decisão

Adotar Spec-Driven Development: toda funcionalidade é especificada em `spec/` (`SPEC-NNN-slug.md`) antes da implementação, e a implementação segue a spec. Desvios exigem atualizar a spec primeiro. Decisões de arquitetura são registradas separadamente como ADRs em `docs/adr/`.

## Consequências

- Rastreabilidade entre requisito, design e código; o material em `/home/neves/Workspace/provimento` é a fonte referenciada pelas specs.
- Custo adicional de escrever/atualizar specs, aceitável pelo ganho de alinhamento.
- Assistentes de código (ver `CLAUDE.md`) devem consultar as specs antes de implementar e apontar conflitos antes de codar.
