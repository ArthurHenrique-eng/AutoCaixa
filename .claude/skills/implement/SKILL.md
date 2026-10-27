---
name: implement
description: Implementa uma mudança solicitada com escopo controlado e verificações; use ao editar código do AutoCaixa.
---

# /implement — AutoCaixa

## Passos
1. Confirmar objetivo, critérios de aceite e estado do Git; preservar trabalho do usuário.
2. Ler o contexto e políticas pertinentes; escolher skills específicas de backend, database, security, testing, frontend ou integrations.
3. Definir a menor mudança suficiente e inspecionar os pontos de entrada/saída.
4. Implementar incrementalmente; não reestruturar áreas não relacionadas nem adicionar dependências sem justificativa.
5. Testar os fluxos alterados e casos negativos usando ambiente isolado.
6. Revisar diff, autorização, transações, compatibilidade, documentação e logs.
7. Reportar arquivos alterados, comandos e resultados reais, limitações e riscos residuais.

## Guardrails
Pagamentos continuam simulados. Não habilitar gateway real nem cobrar. Manter dinheiro em centavos, estado financeiro validado no servidor e efeitos de estoque idempotentes. Não commitar, fazer push, deploy ou operações destrutivas sem autorização explícita.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
