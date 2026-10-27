---
name: plan
description: Planeja uma mudança antes de implementar; use quando o usuário pedir análise, plano ou proposta antes de editar.
---

# /plan — AutoCaixa

## Resultado esperado
Entregar diagnóstico, escopo, arquivos prováveis, sequência de mudanças, riscos, testes e critérios de aceite. Não implementar nesta etapa.

## Passos
1. Ler CLAUDE.md, AGENTS.md, PROJECT_CONTEXT.md e políticas relevantes.
2. Verificar estado do Git e inspecionar só os arquivos ligados à tarefa.
3. Separar fatos de hipóteses; identificar dependências, banco, segurança, compatibilidade e impacto operacional.
4. Selecionar skills mínimas com autocaixa-skill-router; ler apenas as definições necessárias.
5. Entregar plano curto, ordenado e verificável, incluindo comandos de teste existentes.

## Limites
Não editar código, executar migrations, fazer commits/push/deploy nem chamadas externas. Se o usuário pedir explicitamente planejamento e implementação juntos, faça um plano breve e depois implemente.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
