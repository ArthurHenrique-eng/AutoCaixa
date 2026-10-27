---
name: domain-modeling
description: Modela entidades, invariantes e regras de negócio. Use para vendas, produtos, reservas, pagamentos, usuários e estoque.
---

# domain-modeling

## Objetivo e acionamento
Modela entidades, invariantes e regras de negócio. Use para vendas, produtos, reservas, pagamentos, usuários e estoque.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/06_ARQUITETURA_LIMPA.md e autocaixa/Prompt/07_ESTRUTURA_E_PADROES_DE_CODIGO.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Identifique entidades, valores, invariantes e transições.
2. dinheiro em centavos.
3. estoque nunca negativo.
4. diferencie venda, cobrança e pagamento.
5. defina regras no servidor.
6. evite que templates ou handlers decidam estado de domínio.

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
