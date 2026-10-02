# SPEC-005 — Propostas persistidas e painel do sysadmin

- **Status:** Implementada (rev. 3)
- **Data:** 2026-09-30
- **Referências:**
  - [SPEC-002 — Canais de contato](SPEC-002-canais-de-contato.md) (RF-01 a RF-03 e RF-07, que esta spec substitui na parte de persistência e e-mail)
  - [ADR-005](../docs/adr/ADR-005-monolito-laravel-bootstrap.md) e [ADR-006](../docs/adr/ADR-006-multitenancy-e-papeis.md)

## Objetivo

Primeira entrega do monolito Laravel: a solicitação de proposta passa a ser gravada no banco próprio (sem API externa, sem `localStorage`, sem Web3Forms), e um sysadmin da nordit analisa as submissões numa área restrita.

## Requisitos

### RF-01 — Formulário (mantido da SPEC-002)

`/solicitar-proposta` com os mesmos campos, textos e visual do protótipo Vue: nome completo, serventia e e-mail (obrigatórios), telefone/WhatsApp, áreas de interesse (as 8 áreas, tabela `interest_areas`) e mensagem (opcionais). A validação é no servidor, com mensagens em pt-BR por campo e os valores preenchidos preservados. Envio limitado a 5 por minuto por IP.

### RF-02 — Protocolo e persistência

Cada envio grava uma linha em `proposals` com status **Recebida**, uma entrada no histórico (`proposal_status_changes`) e as áreas escolhidas. O protocolo `NRD-AAAA-NNNN` é sequencial por ano e gerado no servidor com contador travado (`protocol_sequences`). A tela de confirmação mostra o protocolo e o link "Consultar solicitação".

### RF-03 — Consulta pública

`/consultar-solicitacao?protocolo=…` mostra protocolo, solicitante, data de envio e a linha do tempo (Recebida → Em análise → Proposta enviada) com o **status real**, alterado pelo sysadmin. A linha do tempo vai até a etapa mais avançada registrada no histórico. Nos status de encerramento (rev. 2), aparece uma mensagem final:

- **Contratada**: "Proposta aceita! Nossa equipe entrará em contato…";
- **Não avançou** ou **Arquivada**: "Esta solicitação foi encerrada…".

A observação interna nunca aparece aqui. O link antigo `/solicitacao/{protocolo}` redireciona para a consulta.

### RF-03a — Status (rev. 2)

Recebida, Em análise, Proposta enviada, **Contratada**, **Não avançou** e **Arquivada**. Só Recebida e Em análise contam como "em aberto" (RF-08).

### RF-07 — Aviso por e-mail (substitui o Web3Forms)

A cada nova solicitação, o Laravel envia um e-mail para `NORDIT_LEADS_MAIL_TO` com todos os campos. O "responder" vai direto para o solicitante. É não bloqueante: falha no envio não impede o protocolo. Sem destinatário configurado, nada é enviado.

### RF-08 — Duplicidade

Se o mesmo e-mail já tem solicitação **em aberto** (Recebida ou Em análise), nada é criado e a tela mostra o protocolo existente com o aviso "Já existe uma solicitação em aberto…". Depois de "Proposta enviada", um novo pedido gera um protocolo novo.

### RF-09 — Papéis e sysadmin

Papéis `super_admin`, `tenant_admin` e `tenant_user` (ADR-006). Login por e-mail e senha em `/entrar`, com bloqueio de 15 minutos após 5 tentativas erradas e mensagem genérica.

Seed do sysadmin (rev. 2): `php artisan db:seed` sempre cria o sysadmin inicial.

- E-mail: `SYSADMIN_EMAIL` ou o padrão `sysadmin@nordit.com.br`.
- Senha: `SYSADMIN_PASSWORD` ou, sem ela, uma senha aleatória exibida **uma única vez** no terminal.
- O seed é idempotente e nunca troca a senha de uma conta existente.
- Para recuperar o acesso: `php artisan sysadmin:reset-password [email]`.

### RF-09a — "Disponível em breve" (rev. 2)

- A raiz `/` mostra a página "Disponível em breve" da nordit, com "Falar com o assistente" (abre o chat da SPEC-006) e "Prefiro preencher o formulário".
- Usuários de instituições (`tenant_admin`/`tenant_user`) veem "Área do cliente em construção" ao entrar.
- Não há telas de usuário nem de gestão de instituições nesta fase. O único painel é o de propostas do sysadmin.

### RF-10 — Lista de propostas (sysadmin)

`/admin/propostas`, só para o super_admin (demais papéis: 403). Tabela com Enviada em, Protocolo, Nome, Serventia, E-mail, Telefone, Áreas, Mensagem e Status (as colunas que a equipe usava no painel do Web3Forms). Totais por status no topo, filtros (busca por protocolo/nome/serventia/e-mail, status, área, período) e paginação de 20.

### RF-11 — Detalhe e mudança de status

`/admin/propostas/{public_id}` mostra todos os dados, o link de WhatsApp e o histórico. O sysadmin muda o status com uma observação interna opcional. Cada mudança entra no histórico com o autor e a data.

### RNF

- Proteção anti-robô por campo-isca (honeypot): o envio é descartado em silêncio, e o robô vê a tela de sucesso sem protocolo.
- Telefone gravado só com dígitos; formatação apenas na tela.
- IP e user-agent do envio guardados para análise de abuso (exibidos só ao sysadmin).

## Fora do escopo (próximas specs)

- Migrar Home, Como funciona e Dúvidas (SPEC-001/003/004); por ora a raiz é "Disponível em breve". O chat está na SPEC-006.
- CRUD de instituições (tenants) e de usuários, recuperação de senha pela tela e área logada do cliente.
- Exportar CSV das propostas, e-mail de confirmação para o solicitante, importação do histórico do Web3Forms/API antiga.

## Critérios de aceitação

Cobertos por `tests/Feature/ProposalSubmissionTest.php` e `tests/Feature/AdminProposalTest.php` (`php artisan test`).

- [x] Envio válido grava a proposta com protocolo `NRD-AAAA-NNNN`, status Recebida, áreas e histórico.
- [x] Campos obrigatórios e formatos inválidos voltam com mensagens em pt-BR, sem gravar.
- [x] E-mail aberto duplicado devolve o protocolo existente; depois de "Proposta enviada" cria outro.
- [x] Com `NORDIT_LEADS_MAIL_TO`, a equipe recebe o e-mail; sem ela, nada é enviado.
- [x] Honeypot preenchido não grava nada.
- [x] Consulta mostra o status atual; protocolo inexistente mostra mensagem amigável.
- [x] Seeder cria o sysadmin (idempotente) e os três papéis.
- [x] Visitante vai para o login; tenant_admin/tenant_user recebem 403 nas propostas.
- [x] Sysadmin lista, filtra e muda o status; a consulta pública reflete a mudança sem expor a observação.

Rev. 2:

- [x] Status Contratada, Não avançou e Arquivada; a consulta mostra a mensagem de encerramento e só as etapas atingidas.
- [x] Seed sem `.env` cria `sysadmin@nordit.com.br` com senha gerada e exibida uma vez; rodar de novo não troca a senha.
- [x] `sysadmin:reset-password` gera uma senha nova.
- [x] A raiz mostra "Disponível em breve" com o chat e o formulário como alternativa.

## Revisões

- **rev. 1 (2026-09-30):** persistência, e-mail, papéis e painel do sysadmin — implementada.
- **rev. 2 (2026-09-30):** novos status, seed com e-mail padrão e senha gerada, raiz "Disponível em breve" — implementada.
- **rev. 3 (2026-10-02):** a nordit passa a atender só cartórios. Sai a pergunta de tipo (formulário, chat, filtro e coluna do painel, e-mail); `InstitutionType` fica só com `cartorio`, gravado automaticamente em toda proposta — implementada.
