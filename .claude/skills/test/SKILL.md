---
name: test
description: Escolhe e executa testes adequados ao escopo; use após código novo, correção de bug ou mudança de contrato.
---

# /test — AutoCaixa

## Passos
1. Descubra comandos atuais em PROJECT_CONTEXT.md e autocaixa/README.md; confirme scripts e versões antes de rodar.
2. Escolha testes lógicos, integração com banco, smoke HTTP ou E2E conforme a mudança.
3. Use banco e credenciais de teste. Nunca aponte testes destrutivos para produção.
4. Cubra sucesso, input inválido, autorização negada, limites, duplicidade e falha/timeout se pertinente.
5. Execute comandos viáveis e registre saída/código de retorno; não alegue execução sem executar.
6. Diagnostique falhas antes de alterar testes; não remova proteções para obter sucesso.

## AutoCaixa
O contexto documenta tests/run_tests.php, tests/run_admin_tests.php e tests/smoke_http.sh; confirme caminhos, dependências e ambiente atuais. No Windows/PowerShell, adapte somente após confirmar PHP/Bash disponíveis. Pagamentos ficam simulados.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
