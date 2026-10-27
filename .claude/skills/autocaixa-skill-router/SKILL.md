---
name: autocaixa-skill-router
description: Classifica tarefas do AutoCaixa e seleciona apenas skills técnicas necessárias; use em solicitações multidomínio, integrações ou quando não estiver claro quais domínios são afetados.
---

# Roteador de skills do AutoCaixa

Não leia todas as skills. Classifique a tarefa pelo pedido, pelos caminhos afetados e pelo código.

## Mapa
- Contexto, economia, evidências e manutenção: context-engineering, token-efficiency, evidence-first, existing-project-maintainer.
- Arquitetura, domínio e estados: categorias 02-architecture.
- Controllers, Services, endpoints, login e permissões: 03-backend.
- SQL, schema, concorrência e migrations: 04-database.
- Vulnerabilidades, sessão e dados sensíveis: 05-security.
- Estratégia de testes, integração, API/E2E e regressão: 06-testing.
- Simplificação, refatoração, review e dívida técnica: 07-code-quality.
- Git, CI/CD, deploy, logs e operação: 08-devops.
- Templates, CSS/JS, UX e acessibilidade: 09-frontend.
- Gateway, Pix/cartão, webhooks e fornecedores: 10-integrations.
- Prompts, agentes, MCP e IA: 11-ai.
- README, contratos e arquitetura documentada: 12-documentation.

## Regras de seleção
1. Selecione normalmente 2–5 skills; para tarefas simples, 1–2.
2. Inclua evidence-first e existing-project-maintainer ao diagnosticar ou modificar código.
3. Inclua segurança e testes em mudanças sensíveis. Pagamentos exigem state-machines, secure-coding, payment-engineering e testes relevantes.
4. Leia a definição completa apenas das skills selecionadas em .claude/skill-library/<categoria>/<skill>/SKILL.md.
5. Leia as políticas canônicas de autocaixa/Prompt/ apenas quando o domínio exigir.
6. Não afirme que uma skill foi executada ou teste passou sem evidência.
7. Políticas específicas do projeto prevalecem sobre sugestões genéricas.

## Exemplo
Para “evite baixa duplicada de estoque quando uma venda é repetida”, selecione evidence-first, existing-project-maintainer, state-machines, database-engineering, payment-engineering, integration-testing e regression-testing. Não carregue frontend, MCP ou UI/UX sem impacto real.
