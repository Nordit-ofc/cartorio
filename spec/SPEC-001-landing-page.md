# SPEC-001 — Landing page do site nordit (protótipo)

- **Status:** Implementada (protótipo, incl. rev. 3); rev. 4 aguardando migração
- **Data:** 2026-07-08
- **Referências:**
  - Design aprovado: `/home/neves/Workspace/provimento/design/Nordit - design.pdf` e `/home/neves/Workspace/provimento/design/Design web Nordit/Nordit - Direções.dc.html` (direção 1b "Moderno & Acessível")
  - Conteúdo normativo: `/home/neves/Workspace/provimento/documentos/` (Provimento 213/2026 e áreas de TI)
  - Estrutura: `/home/neves/Workspace/provimento/spec/Guia_Estrutura_Projetos_Vue.pdf`

## Objetivo

Página inicial do site da nordit apresentando a oferta de TI terceirizada para cartórios, ancorada na conformidade com o Provimento 213/2026 do CNJ. O protótipo reproduz fielmente o design aprovado (desktop e mobile), com placeholders demarcados nas áreas de ilustração.

## Sistema visual

- Paleta: Marinho `#0F2E4D`, Teal `#159B86`, tons de menta (`#e4f3ef`, `#eef7f4`, `#f4faf8`).
- Tipografia: Poppins (títulos), DM Sans (texto e botões).
- Botões-pílula, cantos arredondados, sombras suaves.
- Ícones sólidos em SVG (extraídos do design).

## Requisitos

### RF-01 — Navbar
Barra branca flutuante com: logo `nordit` ("it" em teal), que leva à Home ao ser clicada; links Serviços, Como funciona e Dúvidas; botão-pílula "Entrar" (menu suspenso); botão primário "Solicitar proposta".

> **rev. 2:** o link "Planos" foi removido (a oferta de planos será planejada depois; o foco atual é captar clientes pelo Provimento 213). "Como funciona" navega para a página da SPEC-003; "Dúvidas" permanece `#` até a aprovação da SPEC-004.

- O item **Serviços** abre, ao passar o mouse, um mega-menu em duas colunas com as 8 áreas de serviço (Infraestrutura & Ambiente, Redes & Conectividade, Segurança da Informação, Backup & Continuidade, Monitoramento & Auditoria, LGPD & Conformidade, Integração & Interop., Governança de TI), cada uma com ícone e descrição curta.
- O botão **Entrar** abre, ao clicar, um menu com "Acessar a plataforma" (login de clientes) e "Consultar solicitação" (acompanhar proposta). Clicar fora fecha o menu.

### RF-02 — Hero
Badge "TI para cartórios"; título "TI completa e terceirizada para *cartórios*" (destaque em teal); parágrafo sobre a conformidade com o Provimento 213/2026; botões "Solicitar proposta" (primário) e "Como funciona" (contorno); placeholder de ilustração "[ ilustração · segurança de dados ]".

> **rev. 2 — novo posicionamento (ecossistema, foco em cartórios):** badge "Ecossistema de TI para cartórios"; título "Toda a TI do seu cartório em *um só lugar*" (destaque em teal); parágrafo sobre a fragmentação da TI em vários fornecedores e a concentração no ecossistema nordit, em conformidade com o Provimento 213/2026. O placeholder deu lugar à imagem real `src/assets/images/equipe-nordit.jpg` (desktop e mobile); o botão "Como funciona" navega para a página da SPEC-003. Textos do hero centralizados em `constants/services.js` (`HOME_HERO`).
>
> **rev. 3:** o badge "Ecossistema de TI para cartórios" foi removido do hero — o título passa a abrir a seção diretamente.

### RF-03 — Como funciona (3 passos)
Três cards numerados: 1. Diagnóstico gratuito, 2. Plano sob medida, 3. Implantação & monitoria.

### RF-04 — O que fazemos
Seção com eyebrow "O QUE FAZEMOS", título "Tudo o que o Provimento 213 exige, num só lugar" e grade 3×2 de cards de serviço: Backup & Continuidade, Segurança da Informação, LGPD & Conformidade, Infraestrutura & Redes, Monitoramento & Auditoria, Governança de TI.

> **rev. 3:** título e eyebrow trocam de papel — o destaque agora é o título "O que fazemos" (h2, sem eyebrow acima); abaixo, um subtítulo sem menção ao Provimento: "Tudo o que seu cartório precisa, num só lugar." Textos em `constants/services.js` (`HOME_SERVICES_HEADER`).

### RF-05 — Faixa CTA
Faixa em marinho com título "Comece pelo diagnóstico gratuito", subtítulo e botões "Solicitar proposta" (primário) e "Consultar solicitação" (contorno claro).

### RF-06 — Chat flutuante
Balão circular teal fixo no canto inferior direito, com etiqueta "Fale com a gente 👋" (somente desktop). Sem funcionalidade nesta fase.

## Responsividade (breakpoint 900px)

No mobile, conforme o mock:

- Navbar reduzida: logo, pílula "Entrar" e menu hamburger (painel simples com os links).
- Hero: título curto "TI completa para cartórios", parágrafo resumido, botões empilhados em largura total, ilustração menor "[ ilustração · dados ]" abaixo dos botões.
- Seções "Como funciona" e faixa CTA não aparecem.
- Serviços: lista vertical com 2 destaques (Backup & Continuidade, Segurança da Informação) e link "Ver todos os serviços →" que expande as 8 áreas.

## Fora do escopo (fase de protótipo)

- Página Planos (removida da navbar na rev. 2) e Dúvidas/Suporte (link `#` até a SPEC-004). A página Como funciona é tratada na SPEC-003.
- Chatbot funcional, login/autenticação real, formulário de proposta (tratados na SPEC-002).
- Backend/API e integrações.
- Ilustrações definitivas nas áreas ainda demarcadas com `ImagePlaceholder` (o hero já usa imagem real desde a rev. 2).

## Critérios de aceitação

- [x] Layout desktop e mobile fiéis aos mocks aprovados (paleta, tipografia, espaçamentos e textos exatos).
- [x] Menu Serviços abre por hover com as 8 áreas; menu Entrar abre por clique e fecha ao clicar fora.
- [x] Hamburger mobile abre/fecha o painel de navegação.
- [x] "Ver todos os serviços →" expande a lista no mobile.
- [x] `npm run build` sem erros.

Rev. 2:

- [x] Navbar sem "Planos"; "Como funciona" (navbar, hero e menu mobile) navega para `/como-funciona`.
- [x] Hero com o novo posicionamento (ecossistema · foco em cartórios) e imagem real no lugar do placeholder.

Rev. 3:

- [x] Hero sem o badge acima do título.
- [x] Seção "O que fazemos" com o título em destaque e subtítulo sem menção ao Provimento 213.

## Revisões

- **rev. 1 (2026-07-08):** versão inicial — implementada.
- **rev. 2 (2026-07-13):** remoção da aba "Planos"; hero reposicionado para o ecossistema com foco em cartórios; imagem real no hero; link "Como funciona" ligado à SPEC-003 — implementada.
- **rev. 3 (2026-07-13):** remoção do badge do hero; seção "O que fazemos" com o título em destaque e subtítulo sem referência ao Provimento 213 — implementada.
- **rev. 4 (2026-10-02):** a nordit passa a atender só cartórios: textos do hero sem "prefeituras" e "serviços públicos" — aplicar na migração para Laravel.
