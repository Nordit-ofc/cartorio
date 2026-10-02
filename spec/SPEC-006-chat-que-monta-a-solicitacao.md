# SPEC-006 — Chat que monta a solicitação de proposta

- **Status:** Implementada (rev. 2)
- **Data:** 2026-09-30
- **Referências:**
  - [SPEC-002 — Canais de contato](SPEC-002-canais-de-contato.md) (RF-04 chat roteirizado e RF-05 WhatsApp, que esta spec evolui)
  - [SPEC-005 — Propostas persistidas e painel do sysadmin](SPEC-005-propostas-persistidas-e-painel-sysadmin.md)

## Objetivo

O caminho principal para pedir uma proposta é conversar com o assistente da plataforma. O chat faz as perguntas do formulário uma a uma, monta a solicitação, envia para o mesmo backend (protocolo, banco e e-mail à equipe) e devolve o protocolo na conversa. Nesta fase o chat é **roteirizado, sem IA**. A troca por um assistente com IA é uma evolução futura e reaproveita o mesmo endpoint.

## Requisitos

### RF-04 — Chat roteirizado (evolução da SPEC-002)

- Balão fixo no canto inferior direito, em todas as páginas públicas, com o mesmo visual do `ChatWidget.vue`: cabeçalho marinho, "online agora", indicador "digitando…".
- Opções iniciais: **Quero uma proposta** (inicia o RF-12), **Dúvidas sobre o Provimento 213**, **Acompanhar minha solicitação** (pede o protocolo e abre a consulta) e **Falar no WhatsApp** (RF-05).
- Histórico, passo atual e respostas persistem durante a sessão da aba (`sessionStorage`).
- O roteiro e todos os textos ficam em `config/chat.php`, nunca no JS.

### RF-12 — Montagem da solicitação pela conversa

1. O assistente pergunta, nesta ordem: nome completo, serventia, e-mail, telefone (opcional, botão "Pular"), áreas de interesse (botões de seleção múltipla + "Concluir") e mensagem (opcional).
2. Cada resposta é conferida no navegador (obrigatório, formato de e-mail, telefone com DDD, tamanho). Resposta inválida gera uma mensagem amigável, e a mesma pergunta continua aberta.
3. Ao final, o assistente mostra um **resumo** com as opções "Enviar solicitação", "Corrigir (recomeçar)" e "Prefiro preencher o formulário".
4. "Enviar" faz `POST /solicitar-proposta` em JSON com `source=chat`. O servidor aplica as mesmas regras do formulário (`StoreProposalRequest`) e responde:
   - `201 {protocol, duplicate: false, tracking_url}`: o assistente mostra o protocolo e oferece "Acompanhar solicitação";
   - `200 {duplicate: true}`: o assistente mostra o protocolo em aberto já existente (RF-08 da SPEC-005);
   - `422`: o assistente mostra a mensagem do servidor e volta à pergunta do campo recusado;
   - `419`, `429` ou erro de rede: mensagem específica (sessão expirada, muitas tentativas ou tentar de novo), com o resumo mantido para reenviar.
5. A proposta guarda o canal (`source`: formulário ou chat), exibido no painel do sysadmin e no e-mail à equipe.

### RF-13 — Formulário como alternativa

`/solicitar-proposta` continua funcionando como antes, para quem prefere não conversar ou usa tecnologia assistiva. É acessível pela página inicial, pelo botão da navbar e pelo resumo do chat.

### RNF

- Tudo que o visitante digita entra no DOM como texto (`textContent`), nunca como HTML.
- O envio usa o token CSRF da página (`meta[name=csrf-token]`) e o mesmo limite de 5 envios por minuto.

## Fora do escopo

- IA no chat (futuro: substitui o roteiro por um endpoint conversacional, mantendo `POST /solicitar-proposta` como destino).
- Texto livre fora das perguntas, atendimento humano ao vivo, histórico entre dispositivos.

## Critérios de aceitação

- [x] "Falar com o assistente", na página inicial, abre o chat já no fluxo de proposta (verificado no navegador).
- [x] Respostas inválidas são recusadas com mensagem, e a pergunta continua (verificado no navegador).
- [x] O resumo lista todas as respostas; "Enviar" grava a proposta com `source=chat` e mostra o protocolo (teste `test_rf12_*` e navegador).
- [x] Duplicidade devolve o protocolo existente, e erros de validação voltam em pt-BR (testes `test_rf12_*`).
- [x] "Acompanhar minha solicitação" leva à consulta com o protocolo digitado (verificado no navegador).
- [x] O histórico sobrevive ao recarregar a página (verificado no navegador).

## Revisões

- **rev. 1 (2026-09-30):** chat roteirizado que monta a solicitação — implementada.
- **rev. 2 (2026-10-02):** a nordit passa a atender só cartórios; sai a pergunta "É um cartório ou uma prefeitura?" — implementada.
