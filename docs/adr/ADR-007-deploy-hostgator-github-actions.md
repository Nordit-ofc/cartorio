# ADR-007 — Deploy no HostGator via GitHub Actions

- **Status:** Aceito
- **Data:** 2026-10-13
- **Complementa:** ADR-005 (hospedagem em aberto)

## Contexto

O ADR-005 deixou o host em aberto: a Vercel não roda PHP. O site já está publicado em nordit.com.br numa hospedagem compartilhada do **HostGator** (cPanel, PHP 8.3, MySQL, acesso SSH em `jailshell`), atrás da Cloudflare. A publicação foi manual e o repositório não tinha CI.

Particularidades do servidor:

- `nordit.com.br` é o **domínio principal** da conta, então a raiz do documento é fixa em `~/public_html`. O Laravel inteiro está dentro dela, e um `.htaccess` na raiz (só do servidor) reescreve tudo para `public/`. Os arquivos internos (`.env`, `composer.json`, `storage/`, `vendor/`…) respondem 403/404.
- A mesma conta hospeda outros sites (`rlmsoft.dev`, subdomínios `rce-*`, `.deploy-staging/`), em pastas fora de `public_html`.
- Não há `composer` no PATH do servidor.

## Decisão

- **CI** (`.github/workflows/ci.yml`) em todo push e pull request: `pint --test` e `php artisan test` (SQLite em memória), com PHP 8.3.
- **Deploy** (`.github/workflows/deploy.yml`) quando o CI passa num push na `main`, ou manualmente (`workflow_dispatch`), no environment `production` do GitHub:
  1. `composer install --no-dev` no runner (o servidor não precisa de Composer).
  2. `php artisan down`, envio por **rsync via SSH** para `DEPLOY_PATH` e, no servidor, `migrate --force`, os seeders idempotentes `RoleSeeder` e `InterestAreaSeeder`, `optimize` e `up`.
  3. Permissões: 755/644 no código e 775 em `storage/` e `bootstrap/cache/`.
- **Layout mantido:** `DEPLOY_PATH` é o próprio `~/public_html`. O rsync usa `--delete` só sobre o que vem do repositório e **exclui** o que é do servidor: `.env`, `storage/`, `.htaccess` da raiz, `cgi-bin/`, `.well-known/`, `app-ads.txt` e o binário `composer`. Nada fora de `DEPLOY_PATH` é tocado.
- **Credenciais** só em secrets do environment `production`: `SSH_HOST`, `SSH_PORT`, `SSH_USER`, `SSH_KEY` (chave dedicada ao deploy, sem passphrase) e `DEPLOY_PATH`. O `.env` de produção vive só no servidor.
- O `SysadminSeeder` **não** roda no deploy: o repositório é público, e a senha gerada na primeira execução apareceria no log do Actions. O sysadmin de produção é criado ou redefinido à mão (`php artisan sysadmin:reset-password`).

## Consequências

- Todo push na `main` com CI verde publica o site. Para publicar sem push, use "Run workflow" no Actions.
- O site fica alguns segundos em manutenção (503) durante o deploy. Se o deploy falhar no meio, um passo final roda `php artisan up`.
- Mover o Laravel para fora do `public_html` (por exemplo `~/nordit`, com `public_html` apontando para `nordit/public`) é uma melhoria futura. Basta trocar `DEPLOY_PATH` e o layout no servidor.
- O host key do servidor é obtido com `ssh-keyscan` a cada execução. Se o HostGator trocar de servidor, o deploy continua funcionando, mas sem verificação prévia da identidade do host.
