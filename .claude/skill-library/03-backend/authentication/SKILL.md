---
name: authentication
description: Implementa e revisa autenticação e ciclo de sessão. Use em login, logout, senha, recuperação ou sessão do totem/admin.
---

# authentication

## Objetivo e acionamento
Implementa e revisa autenticação e ciclo de sessão. Use em login, logout, senha, recuperação ou sessão do totem/admin.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md, autocaixa/Prompt/02_SEGURANCA_PHP.md e autocaixa/Prompt/10_APIS_E_INTEGRACOES.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Verifique hashes e algoritmo documentados.
2. nunca armazene senha em texto.
3. regenere ID de sessão após login/privilégio.
4. expire e invalide sessão no servidor.
5. proteja CSRF.
6. limite tentativas.
7. não registre credenciais.
8. preserve isolamento entre compradores no totem.

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
