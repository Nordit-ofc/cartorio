# CLAUDE.md

Contexto para assistentes de IA que trabalham neste repositório. Mantenha-o atualizado quando um padrão mudar.

## Visão geral

Site e plataforma da **nordit** (nordit.com.br), empresa de TI que atende cartórios extrajudiciais (não atende mais prefeituras), com serviços orientados à conformidade com o **Provimento 213/2026 do CNJ**.

Este é o **monolito Laravel** que substitui o protótipo Vue (repositório irmão `../nordit`, já fora do ar). A migração é incremental (ADR-005). Todo conteúdo voltado ao usuário é em pt-BR.

Estado atual:

- A raiz `/` mostra "Disponível em breve".
- O pedido de proposta é feito pelo **chat roteirizado** (SPEC-006), com o formulário como alternativa.
- As propostas vão para o banco e geram um e-mail à equipe (SPEC-005).
- O único painel é o de propostas do sysadmin.
- Usuários das instituições veem "em breve" ao entrar. Não crie telas de usuário ou de gestão sem spec.

## Stack

Laravel 13 (PHP 8.3+), Blade, **Bootstrap 5.3 via CDN**, JavaScript puro em `public/js`, MySQL 8 (SQLite nos testes) e `spatie/laravel-permission`. **Sem Node**: não há `package.json`, Vite nem `@vite` nas views, e não se deve reintroduzi-los.

## Comandos

```bash
composer install
php artisan migrate --seed    # papéis, áreas e sysadmin (sysadmin@nordit.com.br; senha gerada e exibida 1x)
php artisan sysadmin:reset-password [email]   # nova senha para um sysadmin
php artisan serve             # http://localhost:8000
php artisan test              # suíte de aceitação (SQLite em memória)
./vendor/bin/pint             # formatação
```

Ambiente recomendado: devcontainer em `.devcontainer/` (PHP 8.4 + MySQL 8.4).

## Arquitetura por camada

Mesmo desenho do projeto `rede-cuidados-especiais`:

```
Route (routes/web.php)
  → Controller           — só orquestra (Site\*, Admin\*, Auth\*)
    → FormRequest        — validação e mensagens pt-BR; authorize() delega à Policy
    → DTO (app/DTOs)     — normaliza o array validado (ex.: telefone só dígitos, e-mail minúsculo)
      → Service          — regra de negócio e transação (ProposalService, ProtocolGenerator)
        → Repository     — só leitura/listagem paginada (ProposalRepository)
          → Model        — Eloquent + traits
```

- Estados em **enums** (`App\Enums\ProposalStatus`, `ProposalSource`, `InstitutionType`) com `label()`; o banco guarda o `value`. `ProposalStatus::open()` define o que é "em aberto" (duplicidade), e `publicSteps()` define a linha do tempo pública. `InstitutionType` só tem `cartorio`, gravado automaticamente (o usuário não escolhe tipo).
- Só o `ProposalService` cria proposta ou muda status. Toda mudança grava `proposal_status_changes`.
- Protocolos `NRD-AAAA-NNNN` vêm do `ProtocolGenerator` (contador com lock em `protocol_sequences`). Nunca calcule "maior + 1".

## Multi-tenancy e papéis (ADR-006)

- Tenant = instituição cliente (`tenants`). Banco único, coluna `tenant_id`.
- Model de dado de cliente usa a trait `BelongsToTenant`, que aplica o `TenantScope` e preenche `tenant_id`. O super_admin nunca é filtrado. Não remova a checagem `hasUser()` do scope, pois ela evita recursão.
- Dados globais (sem `tenant_id`): `proposals`, `interest_areas`, `protocol_sequences`.
- Papéis fixos em `App\Support\Roles`: `super_admin` (sysadmin nordit, sem tenant), `tenant_admin` e `tenant_user`. Autorização nas Policies (`ProposalPolicy`); rotas do sysadmin também têm `role:super_admin`.
- Todo model de negócio usa `HasPublicId` (ULID em `public_id`, usado nas URLs) e `Auditable` (`created_by`/`updated_by`).

## Views e front-end

- **Site público**: `layouts/site.blade.php` + `public/css/site.css`, com os tokens do design aprovado (direção 1b "Moderno & Acessível", Teal `#159B86` e Marinho `#0F2E4D`, Poppins/DM Sans, breakpoint 900px) portados do Vue. As classes próprias não colidem com o Bootstrap: `.site-container`, `.site-nav`, `.nd-btn`, `.pill-option`. O site deve seguir o design fielmente.
- **Painel**: `layouts/admin.blade.php` + `public/css/admin.css`. É Bootstrap puro com a paleta nordit por variáveis `--bs-*` e usa Bootstrap Icons.
- Ícones do design: `<x-icon name="…">` (markup em `App\Support\Icons`).
- Menus do site em `config/site.php`. Link para rota ainda não migrada fica oculto (`Route::has`).
- JS só para conveniência (máscara, envio único). Prefira CSS (`:has(input:checked)`) e os componentes do Bootstrap (dropdown, collapse).
- **Chat** (SPEC-006):
  - Roteiro e textos em `config/chat.php`. O `ChatWidgetComposer` resolve URLs e injeta as opções do banco em `window.NORDIT_CHAT`, e o motor é `public/js/chat.js`.
  - O envio vai em JSON para o mesmo `POST /solicitar-proposta` do formulário (`ProposalController::store` responde JSON quando `expectsJson()`), então regras e mensagens ficam num lugar só (`StoreProposalRequest`).
  - No JS, texto do usuário entra só por `textContent`.
  - IA no chat é evolução futura: substitui o roteiro e mantém esse endpoint.

## Configuração

`config/nordit.php` define `NORDIT_LEADS_MAIL_TO` (aviso de nova proposta), `NORDIT_WHATSAPP_NUMBER` e `SYSADMIN_*`. Nenhum e-mail, número ou senha real fica no repositório.

## Processo

- **Spec-Driven Development:** specs em `spec/` (`SPEC-NNN-slug.md`). Antes de implementar, verifique a spec correspondente. Conflito entre pedido e spec deve ser apontado antes de codar. As SPEC-001 a 004 vieram do Vue: o conteúdo vale, e a implementação muda conforme o ADR-005.
- **ADRs** em `docs/adr/` (`ADR-NNN-slug.md`).
- **Testes de aceitação** em `tests/Feature`, nomeados com o ID do requisito (`test_rf08_…`), com fixtures sem faker em `tests/Concerns/BuildsFixtures.php`.
- **Convenções de código e commits:** ver `CONVENTIONS.md` (DRY, KISS, Conventional Commits).
- **Commits somente quando o usuário solicitar explicitamente.**
- **Escopo:** não adicionar páginas, integrações ou features fora das specs.

## Próximas etapas da migração (ADR-005)

Sem ordem definida; cada item precisa de spec aprovada antes:

- Home, Como funciona e Dúvidas (SPEC-001/003/004), que substituem o "Disponível em breve".
- IA no chat.
- Gestão de instituições e usuários, recuperação de senha e área do cliente.
- Deploy (host ainda em decisão; a Vercel não roda PHP).
