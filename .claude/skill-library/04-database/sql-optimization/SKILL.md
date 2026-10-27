---
name: sql-optimization
description: Analisa e otimiza consultas com evidência de desempenho. Use em telas lentas, relatórios, filtros e contagem de estoque.
---

# sql-optimization

## Objetivo e acionamento
Analisa e otimiza consultas com evidência de desempenho. Use em telas lentas, relatórios, filtros e contagem de estoque.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/09_BANCO_DE_DADOS_E_MIGRACOES.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Obtenha SQL e cardinalidade.
2. examine índices e EXPLAIN quando disponível.
3. reduza N+1 e consultas redundantes.
4. pagine listas.
5. selecione apenas colunas necessárias.
6. valide com medição antes/depois.
7. não remova constraints para ganhar velocidade.

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
