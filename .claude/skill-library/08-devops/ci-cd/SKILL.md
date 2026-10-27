---
name: ci-cd
description: Define verificações automáticas para mudanças sem expor segredos. Use em GitHub Actions, gates, lint e testes.
---

# ci-cd

## Objetivo e acionamento
Define verificações automáticas para mudanças sem expor segredos. Use em GitHub Actions, gates, lint e testes.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/12_DEPLOY_INFRA_E_CICD.md e autocaixa/Prompt/15_WORKFLOW_GIT_E_MUDANCAS.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Confirme scripts disponíveis.
2. rode o que existe.
3. não invente pipeline/status.
4. versione ações com segurança.
5. proteja secrets.
6. falhe em gates essenciais.
7. não habilite deploy ou pagamentos reais por acidente.

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
