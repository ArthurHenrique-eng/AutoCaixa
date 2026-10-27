# Sistema de skills do AutoCaixa

## Como funciona
- Skills acionáveis: .claude/skills/<nome>/SKILL.md. Cada pasta é filha direta de .claude/skills para ser descoberta pelo Claude Code.
- Biblioteca categorizada: .claude/skill-library/<categoria>/<nome>/SKILL.md. Contém definições completas organizadas pela taxonomia pedida.
- Instruções do projeto: CLAUDE.md importa AGENTS.md; as regras específicas continuam em autocaixa/Prompt/.
- Workflows: /plan, /implement, /review, /security, /test e /ship.
- Roteador: /autocaixa-skill-router e regras de seleção em CLAUDE.md.

## Por que há duas localizações?
A hierarquia de categorias pedida fica em skill-library. O Claude Code não descobre automaticamente .claude/skills/01-core/context-engineering/SKILL.md porque está a mais de um nível de profundidade. Cada skill possui uma entrada de descoberta em .claude/skills/<nome>/SKILL.md, que instrui Claude a ler a definição canônica somente quando a skill é acionada. Isso preserva descoberta automática e leitura sob demanda.

## Categorias
- **01-core — Core e eficiência:** context-engineering, token-efficiency, evidence-first, existing-project-maintainer
- **02-architecture — Arquitetura:** software-architecture, clean-architecture, domain-modeling, state-machines, architecture-review
- **03-backend — Backend e APIs:** backend-engineering, api-design, rest-api, authentication, authorization
- **04-database — Banco de dados:** database-engineering, schema-design, sql-optimization, migrations
- **05-security — Segurança:** secure-coding, owasp, threat-modeling, security-review, secrets-management
- **06-testing — Testes e qualidade:** test-strategy, unit-testing, integration-testing, api-testing, e2e-testing, regression-testing
- **07-code-quality — Qualidade de código:** clean-code, refactoring, code-review, technical-debt
- **08-devops — Git, CI/CD e operação:** git-workflow, ci-cd, deployment, observability
- **09-frontend — Frontend e experiência:** frontend-engineering, ui-ux, accessibility, web-performance
- **10-integrations — Integrações e pagamentos:** payment-engineering, webhook-engineering, third-party-api
- **11-ai — IA e agentes:** prompt-engineering, agent-engineering, mcp-builder, ai-security
- **12-documentation — Documentação:** technical-documentation, api-documentation, architecture-documentation

## Regras de manutenção
1. Mantenha name e description no YAML frontmatter; a descrição diz o que a skill faz e quando usar.
2. Atualize a definição canônica em skill-library e a entrada acionável correspondente em skills.
3. Leia somente as skills pertinentes. Não carregue o catálogo inteiro para uma tarefa simples.
4. Revise skills como código confiável; alterações no repositório podem afetar as ações de agentes.
5. No Claude Code, use /reload-skills depois de criar a pasta skills durante uma sessão existente.

## Workflows
- /plan: análise e plano sem implementar.
- /implement: alteração focada com testes e revisão.
- /review: revisão read-only do diff e riscos.
- /security: auditoria direcionada com evidências.
- /test: escolhe e executa testes adequados.
- /ship: checklist de readiness; não publica por conta própria.
