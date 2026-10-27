---
name: accessibility
description: Aplica acessibilidade prática ao HTML e aos fluxos. Use em formulários, navegação, dialogs, tabelas e totem touch.
---

# accessibility

## Objetivo e acionamento
Aplica acessibilidade prática ao HTML e aos fluxos. Use em formulários, navegação, dialogs, tabelas e totem touch.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/05_DESIGN_UI_UX.md e autocaixa/Prompt/13_PERFORMANCE_E_ACESSIBILIDADE.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Use landmarks/headings coerentes.
2. labels e mensagens ligadas ao campo.
3. foco visível.
4. teclado.
5. aria-live para status.
6. contraste e alvos touch.
7. não dependa só de cor.
8. verifique zoom e leitor de tela quando possível.

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
