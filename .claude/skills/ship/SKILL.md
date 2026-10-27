---
name: ship
description: Faz checklist final de prontidão para entrega sem publicar por conta própria; use antes de uma release.
disable-model-invocation: true
---

# /ship — AutoCaixa

## Gate pré-entrega
1. Conferir escopo, git diff e ausência de secrets.
2. Rodar testes pertinentes; verificar lint/build apenas se configurados e viáveis.
3. Fazer revisão de código e segurança; conferir documentação e migrations.
4. Confirmar configuração, rollback, backup, observabilidade e smoke test conforme a entrega.
5. Confirmar que a mudança não ativou gateway real; simulação não é homologação financeira.
6. Listar gates aprovados, falhos, não executados e risco residual.

## Autorização
Este workflow é somente readiness/checklist por padrão. Não faça commit, push, release, deploy, migration destrutiva, alteração de infraestrutura nem cobrança real sem autorização explícita para a ação específica. Se deploy for pedido, confirme alvo e procedimento antes de executar.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
