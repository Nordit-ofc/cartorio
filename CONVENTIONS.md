# CONVENTIONS.md

Convenções que devem ser respeitadas em todo o código deste projeto.

## Princípios de código

- **DRY (Don't Repeat Yourself):** Evite duplicar blocos de código; isole lógicas repetidas em funções ou classes.
- **KISS (Keep It Simple, Stupid):** Mantenha o código o mais simples possível. Evite otimizações prematuras ou complexidades desnecessárias.
- **Nomes significativos:** Variáveis e funções devem ter nomes que expressem sua intenção e propósito.
- **Funções pequenas e focadas:** Uma função deve fazer apenas uma coisa, e fazê-la bem.
- **Comentários úteis:** O código em si deve ser autoexplicativo; comentários só devem ser usados para explicar o *porquê* de uma decisão complexa, e não *o quê* o código faz.

## Conventional Commits

As mensagens de commit devem seguir o padrão [Conventional Commits](https://www.conventionalcommits.org/pt-br/): `tipo: descrição no imperativo, em minúsculas`.

Tipos e exemplos:

```
feat: adicionar cadastro de veículos com soft-delete
fix: corrigir validação de CPF no cadastro de usuário
docs: atualizar README com credenciais do admin
refactor: extrair normalização de telefone para Documentos
test: adicionar teste unitário para VeiculoService
chore: atualizar dependências do composer.json
```

- `feat` — nova funcionalidade
- `fix` — correção de bug
- `docs` — mudanças apenas em documentação
- `refactor` — mudança de código que não altera comportamento
- `test` — adição ou correção de testes
- `chore` — tarefas de manutenção (dependências, configuração, build)
