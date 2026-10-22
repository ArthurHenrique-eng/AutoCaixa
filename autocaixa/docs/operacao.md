# Operação — runbook da loja

## Rotina diária

- Conferir o painel: vendas do dia, falhas e estoque baixo.
- Conferir se o backup do dia foi gerado.
- Conferir `https://SEU-DOMINIO/health` retornando `ok`.

## Situações comuns

| Situação | O que fazer |
|---|---|
| Totem mostra "Serviço indisponível" | Verificar a internet do totem e `/health`. Se o problema persistir, acionar o técnico. |
| Cliente diz que pagou e a venda não fechou | Não refazer a venda. Consultar a venda em **Vendas**, no painel, e acionar o atendimento. Nenhuma venda é marcada como paga sem confirmação. |
| Administrador bloqueado após erros de senha | Aguardar 15 minutos, ou outro administrador reativa a conta. |
| Produto não reconhecido no leitor | Verificar se o cadastro está ativo e se o código bate com a etiqueta. |
| Estoque divergente | Registrar um ajuste de inventário com o motivo, no painel, para manter a auditoria. |

## Cancelamentos

- Somente vendas aguardando pagamento podem ser canceladas pelo painel. Nenhum estoque é alterado.
- Vendas já pagas exigem estorno. O estorno depende da integração bancária (Fase 4) e ainda não está disponível.

## Auditoria

Todas as ações sensíveis ficam registradas: login, falhas de login, cadastro e alteração de produtos, ajustes de estoque, cancelamentos, criação e desativação de usuários, e exportações de relatório. Senhas e dados de pagamento nunca são registrados.

## Segurança operacional

- Cada funcionário deve ter a própria conta. Não compartilhe senhas.
- Desative contas de quem deixou a loja.
- Troque a senha do administrador inicial logo após a implantação.
