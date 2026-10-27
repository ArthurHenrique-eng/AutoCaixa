---
name: mcp-builder
description: Projeta ferramentas MCP com contratos e controles de acesso. Use ao construir MCP server/client para GitHub, AutoCaixa ou APIs.
---

# mcp-builder

## Objetivo e acionamento
Projeta ferramentas MCP com contratos e controles de acesso. Use ao construir MCP server/client para GitHub, AutoCaixa ou APIs.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md e autocaixa/Prompt/02_SEGURANCA_PHP.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Defina ferramentas pequenas e tipadas.
2. valide entrada.
3. minimize dados retornados.
4. limite ações destrutivas.
5. documente erros.
6. autentique e autorize.
7. não exponha shell arbitrário.
8. teste chamadas duplicadas/falhas.
9. proteja tokens.

## Regras específicas do AutoCaixa
- Aplicação em autocaixa/, PHP + templates HTML/JS/CSS e MySQL/MariaDB via PDO; confirme implementação real antes de editar.
- A arquitetura documentada inclui app/Core, Controllers, Services, Repositories, Integrations/Payments e Views; mantenha padrões existentes salvo evidência e justificativa.
- Pagamentos estão simulados nesta fase. Não ative cobrança real nem afirme integração de produção sem verificar código.
- Valores monetários em centavos; operações de venda/estoque precisam manter consistência, transações e idempotência.
- Não confie exclusivamente no frontend para preço, permissão ou confirmação de pagamento.
- Não faça commit, push, deploy, migration destrutiva ou operação externa sem autorização explícita para a ação específica.

## Critérios de conclusão
- Inspecione diff e arquivos realmente afetados.
- Execute verificações pertinentes e registre comandos e resultados reais.
- Separe achados confirmados de hipóteses.
- Informe arquivos/linhas, riscos residuais e limitações de ambiente.
- Não declare segurança absoluta, conformidade legal ou prontidão financeira com base em revisão pontual.
