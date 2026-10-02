# nordit — cartorios

Site e plataforma da **nordit**: TI completa e terceirizada para cartórios, com foco na conformidade com o Provimento 213/2026 (CNJ).

Monolito **Laravel 13 + Blade + Bootstrap 5** (sem Node), que substitui o protótipo Vue do repositório `nordit` (ver [ADR-005](docs/adr/ADR-005-monolito-laravel-bootstrap.md)).

## O que já funciona

- `/`: página "Disponível em breve" com o **assistente (chat)**, que monta a solicitação de proposta pergunta a pergunta.
- `/solicitar-proposta`: o mesmo pedido em formulário, como alternativa. Os dois gravam no banco, geram o protocolo `NRD-AAAA-NNNN`, detectam duplicidade e avisam a equipe por e-mail.
- `/consultar-solicitacao`: consulta pública do status pelo protocolo.
- `/entrar` e `/admin/propostas`: login e painel do sysadmin para analisar as solicitações e mudar o status. Usuários das instituições veem "em breve".

Detalhes em [SPEC-005](spec/SPEC-005-propostas-persistidas-e-painel-sysadmin.md) e [SPEC-006](spec/SPEC-006-chat-que-monta-a-solicitacao.md).

## Primeiro acesso (sysadmin)

`php artisan db:seed` cria o sysadmin `sysadmin@nordit.com.br` (ou `SYSADMIN_EMAIL`). Sem `SYSADMIN_PASSWORD` no `.env`, o seed gera uma senha e a mostra **uma única vez** no terminal. Anote-a. Para gerar outra:

```bash
php artisan sysadmin:reset-password sysadmin@nordit.com.br
```

## Como rodar

### Devcontainer (recomendado)

Abra a pasta no VS Code e escolha "Reopen in Container". O ambiente sobe PHP 8.4 e MySQL 8.4, instala as dependências, roda as migrations e os seeders e serve o app em http://localhost:8000. A senha gerada do sysadmin aparece no log do `post-create`.

### Sem Docker

Requer PHP 8.3+ com as extensões `pdo_mysql` (ou `pdo_sqlite`), `mbstring`, `intl`, `fileinfo`, `openssl` e `zip`, além do Composer.

```bash
composer install
cp .env.example .env && php artisan key:generate
# ajuste DB_* (ou DB_CONNECTION=sqlite) no .env
php artisan migrate --seed
php artisan serve
```

### Testes

```bash
php artisan test
```

## Estrutura

```
app/
├── DTOs/            # dados validados e normalizados
├── Enums/           # ProposalStatus, InstitutionType
├── Http/
│   ├── Controllers/ # Site/ (público), Admin/ (painel), Auth/
│   ├── Middleware/  # EnsureActiveAccount
│   └── Requests/    # validação (mensagens pt-BR)
├── Mail/            # NewProposalReceived
├── Models/          # Tenant, User, Proposal, InterestArea…
├── Policies/
├── Repositories/    # consultas de listagem
├── Scopes/          # TenantScope
├── Services/        # ProposalService, ProtocolGenerator
├── Support/         # Roles, Icons
└── Traits/          # HasPublicId, BelongsToTenant, Auditable
config/nordit.php    # e-mail de leads, WhatsApp, sysadmin
config/site.php      # menus do site público
public/css, public/js  # CSS e JS sem build
resources/views/     # layouts/, site/, admin/, auth/, partials/, components/
```

## Documentação

- `spec/`: especificações (Spec-Driven Development).
- `docs/adr/`: decisões de arquitetura.
- `CONVENTIONS.md`: convenções de código e de commits.
- `CLAUDE.md`: contexto para assistentes de IA.
