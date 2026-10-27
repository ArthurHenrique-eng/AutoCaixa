---
name: ui-ux
description: Melhora clareza, consistência e usabilidade sem redesign fora do escopo. Use em painéis, checkout, formulários e feedback visual.
---

# ui-ux

## Objetivo e acionamento
Melhora clareza, consistência e usabilidade sem redesign fora do escopo. Use em painéis, checkout, formulários e feedback visual.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/05_DESIGN_UI_UX.md e autocaixa/Prompt/13_PERFORMANCE_E_ACESSIBILIDADE.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Siga design existente e tokens CSS.
2. estabeleça hierarquia e foco.
3. forneça feedback claro.
4. reduza fricção em tela touch.
5. diferencie ações críticas.
6. limite mudanças às telas pertinentes.

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
