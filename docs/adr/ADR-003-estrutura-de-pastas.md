# ADR-003 — Estrutura de pastas conforme o Guia de Organização de Projetos Vue

- **Status:** Substituído pelo [ADR-005](ADR-005-monolito-laravel-bootstrap.md) (2026-09-30)
- **Data:** 2026-07-08

## Contexto

A empresa padronizou a organização de projetos Vue no documento `/home/neves/Workspace/provimento/spec/Guia_Estrutura_Projetos_Vue.pdf`, que define as pastas `components/{layout,ui,common}`, `layouts`, `views`, `router`, `services`, `stores`, `composables`, `utils`, `constants` e `plugins` dentro de `src/`.

## Decisão

Seguir integralmente a estrutura do guia. Regras de uso adotadas:

- `components/layout` — componentes estruturais (TheNavbar, ChatWidget); `components/ui` — primitivas visuais (BaseButton, BaseBadge, AppIcon, BrandLogo); `components/common` — componentes de negócio compartilhados (ServiceCard, StepCard, ImagePlaceholder).
- `views/<pagina>/components/` — seções exclusivas de cada página (ex.: `views/home/components/HeroSection.vue`).
- `constants/` — todo texto de conteúdo e os ícones SVG; componentes não têm conteúdo hardcoded.
- Pastas ainda sem uso (`services`, `stores`, `utils`, `plugins`) permanecem no repositório com `.gitkeep`, para que a estrutura completa exista desde o início.
- Alias `@` → `src/` configurado no Vite.

## Consequências

- Qualquer desenvolvedor (ou assistente) sabe onde criar cada tipo de arquivo.
- Pastas vazias carregam a intenção do guia, ao custo de arquivos `.gitkeep`.
