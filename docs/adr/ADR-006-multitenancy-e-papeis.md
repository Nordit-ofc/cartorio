# ADR-006 — Multi-tenancy por coluna e três papéis fixos

- **Status:** Aceito
- **Data:** 2026-09-30

## Contexto

A plataforma atenderá várias instituições clientes (cartórios), cada uma com seus usuários e dados isolados. O projeto Vue não tinha autenticação nem papéis. Ao mesmo tempo, as solicitações de proposta da landing são leads da própria nordit, não dados de um cliente.

## Decisão

- **Tenant = instituição cliente** (`tenants`: nome, tipo (hoje só cartório), CNPJ, contato, ativo).
- **Banco único, isolamento por `tenant_id`.** Models de dados de cliente usam a trait `BelongsToTenant`, que aplica o `App\Scopes\TenantScope` (filtra `SELECT` pelo tenant do usuário logado) e preenche `tenant_id` na criação. O super_admin nunca é filtrado.
- **Dados globais ficam sem `tenant_id`:** `proposals`, `interest_areas`, `protocol_sequences`. As propostas são visíveis só ao super_admin (`ProposalPolicy`).
- **Três papéis fixos** via `spatie/laravel-permission` (`App\Support\Roles`):
  - `super_admin` — sysadmin da nordit, sem tenant (`users.tenant_id = null`);
  - `tenant_admin` — administrador de uma instituição;
  - `tenant_user` — usuário comum de uma instituição.
- **Sysadmin inicial** criado pelo `SysadminSeeder`: `sysadmin@nordit.com.br` (ou `SYSADMIN_EMAIL`), com `SYSADMIN_PASSWORD` ou uma senha gerada e exibida uma única vez. É idempotente e nunca troca a senha de uma conta existente; `php artisan sysadmin:reset-password` recupera o acesso.
- Usuário ou instituição inativos têm a sessão encerrada pelo middleware `active.account`.
- URLs usam `public_id` (ULID, trait `HasPublicId`), nunca o `id` incremental.

## Consequências

- Simples de operar (um banco, migrations únicas). O risco de vazamento entre tenants é mitigado pelo scope global, que **todo** model de cliente precisa aplicar (via `BelongsToTenant`), e pelas Policies checando o mesmo `tenant_id`.
- Se aparecer exigência de isolamento físico (banco por cliente), a troca para `stancl/tenancy` deve ser avaliada em novo ADR.
- Papéis fixos bastam por enquanto. Se a plataforma precisar de permissões finas (grupos, permissões avulsas, como no `rede-cuidados-especiais`), o catálogo de permissões entra em novo ADR.
