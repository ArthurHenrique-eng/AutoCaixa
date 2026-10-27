---
name: review
description: Revisa diff por bugs, regressões, segurança e compatibilidade sem editar; use antes de concluir uma tarefa ou revisar código.
---

# /review — AutoCaixa

## Modo somente leitura
Não altere arquivos. Inspecione git diff, arquivos alterados e contexto adjacente necessário.

## Checklist
1. Bugs de lógica, edge cases e erros.
2. Autenticação, autorização/IDOR, CSRF, XSS, SQL injection, secrets e exposição de dados.
3. Transações, concorrência, duplicidade e consistência de venda/estoque/pagamento.
4. Compatibilidade de contratos, configuração, migrations e dados.
5. Testes relevantes faltantes, documentação e complexidade desnecessária.

## Saída
Liste achados acionáveis com severidade, arquivo/linha, evidência, impacto e correção proposta. Se não houver achados, declare escopo e limites da revisão; nunca garanta segurança absoluta.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
