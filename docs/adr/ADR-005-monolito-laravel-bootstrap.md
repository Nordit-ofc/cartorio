# ADR-005 — Monolito Laravel + Blade + Bootstrap, sem Node

- **Status:** Aceito
- **Data:** 2026-09-30
- **Substitui:** ADR-002 (Vue 3 + Vite), ADR-003 (estrutura de pastas Vue), ADR-004 (CSS puro sem framework)

## Contexto

O protótipo em Vue 3 (repositório `nordit`) é uma SPA estática na Vercel, sem backend próprio: as solicitações de proposta iam para uma API externa (`nordit.rlmsoft.dev`) com fallback em `localStorage`, e os leads saíam do navegador para o Web3Forms, com a chave exposta no bundle. A próxima etapa exige API e banco próprios (propostas persistidas, área do sysadmin e, depois, a plataforma multi-tenant dos clientes). A direção do projeto decidiu não usar mais Node.js.

## Decisão

- **Laravel 13 (PHP 8.3+)** como monolito: rotas, controllers, validação, banco e e-mail no mesmo projeto, em um repositório novo (`cartorios`). O repositório Vue fica congelado até a paridade e depois é arquivado.
- **Blade** para todas as telas (site público e painel), renderizadas no servidor (resolve o SEO pendente no ADR-002).
- **Bootstrap 5.3 via CDN** (jsDelivr), sem Sass e sem build. O design aprovado (direção 1b) continua valendo: os tokens de `main.css` viraram custom properties em `public/css/site.css` e alimentam as variáveis `--bs-*` do Bootstrap. O site público mantém classes próprias (`.site-nav`, `.nd-btn`, `.pill-option`…) portadas dos componentes Vue; o painel usa Bootstrap puro com a paleta nordit (`public/css/admin.css`).
- **JavaScript puro** em `public/js/`, só para conveniências de interface (máscara, envio único). Comportamentos do Bootstrap (dropdown, collapse) vêm do `bootstrap.bundle` da CDN. Estados que antes eram reativos no Vue passam a ser CSS quando possível (ex.: pílula ativa com `:has(input:checked)`).
- **Sem Node:** `package.json`, `vite.config.js` e `resources/{css,js}` do esqueleto foram removidos; não há `@vite` nas views.
- **Camadas** (mesmo desenho do projeto `rede-cuidados-especiais`): Route → Controller (orquestra) → FormRequest (validação) → DTO → Service (regras, transação) → Repository (leitura paginada) → Model.

### Mapeamento do Vue para o Laravel

| Vue | Laravel |
|---|---|
| `router/index.js` | `routes/web.php` (mesmas URLs públicas) |
| `views/*View.vue` | `resources/views/site/**` + controllers em `App\Http\Controllers\Site` |
| `components/layout/TheNavbar.vue` | `resources/views/partials/site-navbar.blade.php` |
| `components/ui/AppIcon.vue` + `constants/icons.js` | `<x-icon>` + `App\Support\Icons` |
| `components/ui/BaseButton.vue` | classes `.nd-btn` em `site.css` |
| `constants/services.js` (menus) | `config/site.php` e tabela `interest_areas` |
| `services/*Service.js` + `stores/*.js` | `App\Services`, `App\Repositories` |
| `utils/validators.js` | `App\Http\Requests\*` |
| `VITE_*` (Vercel) | `.env` do servidor (`NORDIT_*`, `MAIL_*`) |

## Consequências

- **Hospedagem:** a Vercel não roda PHP. É preciso um host com PHP 8.3+, MySQL 8 e cron (VPS, Forge, Laravel Cloud ou hospedagem compartilhada). O workflow `.github/workflows/main.yml` da Vercel não foi trazido.
- **Desenvolvimento local:** devcontainer (PHP 8.4 + MySQL 8.4) em `.devcontainer/`, no mesmo molde do `rede-cuidados-especiais`.
- Ícones do painel usam Bootstrap Icons (CDN); o site público mantém os SVGs próprios do design.
- Migração incremental: páginas ainda não migradas (Home, Como funciona, Dúvidas, Plataforma, chat) ficam ocultas na navbar (`Route::has`) até existirem.
