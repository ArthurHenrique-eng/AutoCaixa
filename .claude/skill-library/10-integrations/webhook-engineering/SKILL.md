---
name: webhook-engineering
description: Projeta consumo seguro e idempotente de webhooks. Use ao integrar notificações de provedores ou eventos assíncronos.
---

# webhook-engineering

## Objetivo e acionamento
Projeta consumo seguro e idempotente de webhooks. Use ao integrar notificações de provedores ou eventos assíncronos.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/10_APIS_E_INTEGRACOES.md e autocaixa/Prompt/14_PAGAMENTOS_E_OPERACOES_CRITICAS.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Valide autenticidade conforme documentação oficial atual.
2. não confie só no payload.
3. registre event ID.
4. proteja contra replay/duplicação.
5. tolere atraso e desordem.
6. verifique status no servidor.
7. transacione efeitos.
8. teste eventos falsos/repetidos. Webhook real não está implementado sem evidência atual.

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
