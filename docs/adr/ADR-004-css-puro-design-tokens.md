# ADR-004 — CSS puro com design tokens (sem framework de UI)

- **Status:** Substituído pelo [ADR-005](ADR-005-monolito-laravel-bootstrap.md) (2026-09-30)
- **Data:** 2026-07-08

## Contexto

O design do site foi aprovado com valores exatos (direção 1b "Moderno & Acessível": paleta Teal & Marinho, Poppins/DM Sans, botões-pílula, sombras e raios específicos) e a instrução é reproduzi-lo fielmente. Frameworks como Vuetify ou Tailwind impõem seus próprios sistemas visuais e adicionariam camadas de sobrescrita para chegar ao mesmo resultado.

## Decisão

- Não usar framework de UI/CSS. Estilos em CSS puro, com `scoped` nos componentes.
- Design tokens centralizados como custom properties em `src/assets/styles/main.css` (cores, fontes, raios, sombras).
- Breakpoint único de responsividade em **900px**, com utilitários `.only-desktop`/`.only-mobile` para os blocos que diferem entre os mocks desktop e mobile.
- Fontes servidas pelo Google Fonts (mesma origem usada no design).

## Consequências

- Fidelidade pixel a pixel ao design e bundle pequeno, sem dependências de UI.
- Componentes de UI próprios (`BaseButton`, `BaseBadge` etc.) precisam ser mantidos pelo time.
- Se o design system crescer muito, extrair os tokens para um pacote próprio pode ser avaliado em novo ADR.
