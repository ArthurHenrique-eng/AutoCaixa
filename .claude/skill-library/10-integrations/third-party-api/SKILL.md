---
name: third-party-api
description: Integra APIs externas com contratos, limites e recuperação de falhas. Use em gateway de pagamento e serviços terceirizados.
---

# third-party-api

## Objetivo e acionamento
Integra APIs externas com contratos, limites e recuperação de falhas. Use em gateway de pagamento e serviços terceirizados.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/10_APIS_E_INTEGRACOES.md e autocaixa/Prompt/14_PAGAMENTOS_E_OPERACOES_CRITICAS.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Consulte documentação oficial vigente.
2. defina timeout.
3. trate 4xx/5xx e rate limits.
4. faça retry só em operações seguras/idempotentes.
5. nunca repita cobrança cegamente.
6. nunca codifique secrets.
7. valide resposta.
8. minimize dados enviados.
9. teste falhas e estado desconhecido.

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
