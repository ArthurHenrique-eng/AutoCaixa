@AGENTS.md

# Claude Code — AutoCaixa

## Objetivo
Manter e evoluir o AutoCaixa Local com segurança, mudanças incrementais e evidências, respeitando a arquitetura e políticas já mantidas em autocaixa/Prompt/.

## Instruções de entrada
- Leia AGENTS.md (importado acima), autocaixa/Prompt/PROJECT_CONTEXT.md e autocaixa/Prompt/AUTOCAIXA_REGRAS_ESPECIFICAS.md antes de tarefas de código.
- Consulte autocaixa/Prompt/AGENTS.md para escolher políticas adicionais por assunto. Não leia todos os prompts para toda tarefa.
- Verifique o estado do Git e preserve alterações existentes.
- Não invente arquivos, APIs, versões, comandos, comportamentos ou resultados de testes.

## Skills e workflows
As skills acionáveis ficam em .claude/skills/<nome>/SKILL.md. A biblioteca completa organizada por categoria fica em .claude/skill-library/. Cada skill acionável manda carregar apenas sua definição detalhada quando for necessária.
- Planejamento: /plan
- Implementação: /implement
- Revisão: /review
- Segurança: /security
- Testes: /test
- Preparação para entrega: /ship
- Seleção multidomínio: /autocaixa-skill-router

## Roteamento por assunto
- Contexto/economia/evidências/manutenção: context-engineering, token-efficiency, evidence-first, existing-project-maintainer.
- Arquitetura/domínio/estados: skills de 02-architecture.
- Controllers, Services, rotas, login/permissões: skills de 03-backend.
- SQL, schema, transações e migrations: skills de 04-database.
- Vulnerabilidades, sessões e dados sensíveis: skills de 05-security.
- Estratégia, integração e regressão de testes: skills de 06-testing.
- Simplificação, refatoração, diff e dívida técnica: skills de 07-code-quality.
- Git, CI/CD, deploy e logs: skills de 08-devops.
- Templates, CSS/JS, UX e acessibilidade: skills de 09-frontend.
- Gateway, webhooks e serviços externos: skills de 10-integrations.
- Prompts, agentes, MCP e IA: skills de 11-ai.
- README, contrato API e arquitetura documentada: skills de 12-documentation.
Tarefas multidomínio podem selecionar várias skills. Não carregue todas as 51 skills; escolha o conjunto mínimo suficiente.

## Regras invioláveis
- Raiz da aplicação: autocaixa/. Stack documentada: PHP + templates HTML/CSS/JS + MySQL/MariaDB via PDO. Confirme versões e comandos no contexto e ambiente reais.
- A arquitetura documentada inclui app/Core, Controllers, Services, Repositories, Integrations/Payments, Views, database/migrations, scripts e tests. Inspecione o código antes de alterar.
- Pagamentos seguem em modo simulado nesta fase. Não ative gateway real, não faça cobranças e não afirme integração Cielo/Pix/cartão ou webhook de produção existente sem evidência.
- Dinheiro é representado em centavos; preço, permissão e confirmação financeira são decididos no servidor.
- Resultado de pagamento ambíguo permanece não confirmado até verificação autoritativa. Efeitos de estoque/finalização devem ser transacionais e idempotentes.
- Não adicione dependências, camadas, build tools ou abstrações sem benefício concreto.
- Não faça commit, push, deploy, migration destrutiva, alteração de branch ou operação externa irreversível sem autorização explícita para aquela ação. Não enfraqueça testes ou segurança para obter sucesso aparente.

## Conclusão
Informe resumo, arquivos alterados, verificações efetivamente executadas, resultados reais, limitações e riscos residuais. Nunca declare segurança absoluta ou prontidão para pagamento real com base em revisão pontual.
