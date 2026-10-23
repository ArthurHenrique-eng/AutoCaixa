# Política de pagamentos e operações críticas

Aplique este documento apenas quando o produto possui pagamentos, pedidos, estoque, créditos, reservas, assinaturas ou outro estado com impacto financeiro ou operacional relevante.

## Autoridade do estado
- Nunca use a tela de sucesso do navegador como prova suficiente de pagamento.
- Confirme o estado no backend a partir de uma fonte confiável, conforme o fornecedor (API autenticada, webhook assinado e/ou reconciliação oficial).
- Nunca confie em preço, desconto, total, taxa, papel ou status fornecidos pelo cliente.
- Não use um gateway simulado em produção real. Proteja a ativação por configuração explícita, ambiente e validação de startup.
- Faça o sistema falhar de forma segura quando a configuração do gateway real estiver ausente ou ambígua.

## Idempotência e duplicidade
- Identifique cada intenção/pedido/operação com um ID estável.
- Trate retries e notificações repetidas de maneira idempotente.
- Garanta que venda, pagamento, baixa de estoque, emissão de recibo e créditos não sejam aplicados duas vezes.
- Use constraints/transações/locks adequados para proteger concorrência.
- Não marque uma operação como paga, concluída ou entregue quando o resultado financeiro for desconhecido.

## Estados e reconciliação
Modele estados válidos e transições permitidas, por exemplo: criado, pendente, autorizado, pago, falhou, cancelado, estornado, em reconciliação. Use apenas estados compatíveis com o negócio e o provedor real.

Para timeout depois de submeter uma operação, não presuma falha nem sucesso. Registre o estado desconhecido e consulte a fonte oficial antes de repetir uma ação não idempotente.

## Estoque e pedido
- Reserve/baixe estoque dentro de uma operação atômica compatível com o domínio.
- Defina quando a reserva expira e como é liberada.
- Proteja contra quantidade negativa e compras concorrentes.
- Garanta que uma mesma venda não seja finalizada mais de uma vez.
- Trate cancelamento e estorno como transições explícitas e auditáveis.

## Simulação e testes
- Use ambiente, credenciais e endpoints de sandbox separados.
- Deixe simulações identificáveis e impossíveis de ativar acidentalmente em produção.
- Teste webhooks repetidos, atrasados, inválidos e fora de ordem, além de timeout e indisponibilidade.
- Nunca exponha um endpoint público que permita ao cliente declarar a própria compra como paga.

## Auditoria
Guarde IDs de correlação e eventos operacionais necessários, sem registrar dados de cartão, tokens secretos ou dados pessoais desnecessários. Verifique exigências reais do provedor e do setor em documentação oficial.
