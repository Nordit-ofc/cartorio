# ADR-002 — Vue 3 + Vite como stack do frontend

- **Status:** Substituído pelo [ADR-005](ADR-005-monolito-laravel-bootstrap.md) (2026-09-30)
- **Data:** 2026-07-08

## Contexto

A etapa atual é a prototipação do site da nordit. A direção do projeto definiu Vue.js como tecnologia do frontend, e o protótipo precisa evoluir depois para páginas adicionais (Planos, Como funciona, Dúvidas) e para a plataforma de clientes.

## Decisão

- **Vue 3** com Composition API e `<script setup>` em todos os componentes.
- **Vite** como bundler/dev server.
- **Vue Router** para navegação (SPA) e **Pinia** para estado global, já instalados e inicializados em `main.js`, mesmo que o protótipo use apenas uma rota e nenhuma store — o esqueleto fica pronto para as próximas fases.
- JavaScript (sem TypeScript) nesta fase, mantendo o protótipo simples (KISS).

## Consequências

- SPA sem SSR; se SEO se tornar requisito do site institucional, uma migração para Nuxt ou pre-rendering deverá ser avaliada em novo ADR.
- Dependências mínimas: vue, vue-router, pinia, vite e @vitejs/plugin-vue.
- A adoção de TypeScript pode ser reavaliada quando a plataforma (área logada) começar.
