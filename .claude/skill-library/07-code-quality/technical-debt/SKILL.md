---
name: technical-debt
description: Prioriza dívida técnica por risco e custo, sem reescritas desnecessárias. Use ao encontrar duplicação, padrões frágeis ou gaps de manutenção.
---

# technical-debt

## Objetivo e acionamento
Prioriza dívida técnica por risco e custo, sem reescritas desnecessárias. Use ao encontrar duplicação, padrões frágeis ou gaps de manutenção.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md e autocaixa/Prompt/07_ESTRUTURA_E_PADROES_DE_CODIGO.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Descreva sintoma e impacto.
2. estime risco/custo.
3. separe correção urgente de melhoria futura.
4. proponha menor passo seguro.
5. mantenha pendências baseadas em evidência.
6. não amplie escopo sem necessidade.

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
