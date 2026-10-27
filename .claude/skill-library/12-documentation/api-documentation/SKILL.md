---
name: api-documentation
description: Documenta contratos de endpoints e integrações. Use ao criar/alterar rotas, autenticação, payloads e erros.
---

# api-documentation

## Objetivo e acionamento
Documenta contratos de endpoints e integrações. Use ao criar/alterar rotas, autenticação, payloads e erros.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/11_DOCUMENTACAO.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Documente método/caminho, autenticação/permissão, request/response, status, validações e exemplos.
2. não exponha dados reais.
3. derive do código atual.
4. indique mudanças incompatíveis.
5. esclareça se integração é simulada ou real.

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
