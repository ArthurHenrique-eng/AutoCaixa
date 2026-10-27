---
name: authorization
description: Garante autorização server-side por ação e recurso. Use em painel, produtos, vendas, relatórios, usuários e endpoints.
---

# authorization

## Objetivo e acionamento
Garante autorização server-side por ação e recurso. Use em painel, produtos, vendas, relatórios, usuários e endpoints.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md, autocaixa/Prompt/02_SEGURANCA_PHP.md e autocaixa/Prompt/10_APIS_E_INTEGRACOES.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Aplique menor privilégio.
2. verifique role/permissão em cada operação no servidor.
3. confira propriedade do recurso.
4. não dependa de esconder botão.
5. previna IDOR e escalada de privilégio.
6. negue por padrão.
7. teste cada perfil e acessos indevidos.

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
