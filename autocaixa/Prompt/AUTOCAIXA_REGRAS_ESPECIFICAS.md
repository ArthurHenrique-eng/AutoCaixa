# Regras específicas do AutoCaixa — usar somente neste repositório

> Este documento deriva dos riscos observados numa análise do ZIP do AutoCaixa em 23/10/2026. **É obrigatório revalidar cada item no código atual antes de corrigir**, porque o repositório pode ter mudado desde a análise.

## Fluxos críticos
Trate checkout, sessão de totem, vendas, pagamento, estoque, exportações e configuração de produção como fluxos de alto impacto.

## Pontos para verificar primeiro
1. **Gateway simulado:** confirme se a aplicação ainda escolhe gateway simulado por padrão ou expõe rota pública de aprovação simulada. Em produção real, bloqueie simulação por configuração explícita e falhe ao iniciar se houver modo inseguro. Uma simulação não pode ser confundida com pagamento confirmado.
2. **Finalização:** confirme se pedido/venda só é marcado como pago a partir da fonte autoritativa do provedor, e não por uma rota chamada pelo cliente ou por redirect do navegador.
3. **Idempotência:** confirme que o estoque é atualizado uma única vez, mesmo com duplo clique, repetição de requisição, webhook duplicado, retry ou dois workers concorrentes.
4. **Resultado desconhecido:** em timeout ou falha de conexão após solicitar pagamento, mantenha estado pendente/desconhecido até reconciliar com a fonte autoritativa. Não marque como pago nem repita operação não idempotente cegamente.
5. **Totem/sessão compartilhada:** confirme expiração no servidor, limpeza de carrinho e estado da sessão, regeneração do ID conforme fluxo e impossibilidade de usuário seguinte visualizar ou continuar compra alheia. Timer só no frontend não é controle suficiente.
6. **Sessões/cookies:** confirme flags adequadas, expiração, fixação de sessão, encerramento no servidor e regeneração após mudança de privilégio ou autenticação.
7. **Transações/concorrência:** avalie locks, transações, constraints e estados válidos para preço, quantidade, estoque e finalização.
8. **Cielo ou outro provedor real:** valide assinatura/autenticidade conforme documentação oficial vigente, idempotência, status consultado no servidor, eventos duplicados/atrasados e reconciliação.
9. **CSV/exportações:** neutralize células que possam ser interpretadas como fórmulas por planilhas, sem corromper exportações legítimas.
10. **Hospedagem:** confirme HTTPS, configuração de produção, diretório público restrito, `.env`/backups inacessíveis, logs protegidos, erros não exibidos ao cliente e permissões mínimas no banco.
11. **Rate limiting:** verifique proteção por rota para login, checkout, endpoints públicos e operações custosas.
12. **Testes:** crie cenários para sessão vencida, venda duplicada, concorrência de estoque, pagamento simulado em produção, webhook falso/repetido, falha do provedor e tentativa de reutilizar venda de outra sessão.

## Regras operacionais
- Não faça cobrança real nem altere contas ou credenciais de gateway.
- Não rode migrations nem altere banco de produção sem autorização explícita e plano de reversão.
- Não remova comportamento de pagamento real para fazer testes passarem.
- Não assuma que todos os achados da auditoria passada continuam presentes. Procure evidência no código atual.

## Critério de conclusão
Apresente achados confirmados com arquivo/linha ou fluxo, testes executados, limitações ambientais, risco residual e o que exige validação manual. Não afirme que o sistema está pronto para operar financeiramente sem teste integrado do provedor e validação operacional adequada.
