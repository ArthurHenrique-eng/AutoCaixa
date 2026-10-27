---
name: token-efficiency
description: Reduz leituras, repetições e contexto desnecessário sem sacrificar qualidade. Use em sessões longas, repositórios grandes ou tarefas com custo de contexto alto.
---

# token-efficiency

## Objetivo e acionamento
Reduz leituras, repetições e contexto desnecessário sem sacrificar qualidade. Use em sessões longas, repositórios grandes ou tarefas com custo de contexto alto.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Use buscas direcionadas.
2. leia somente trechos relevantes.
3. não repita código ou logs completos.
4. evite varredura indiscriminada.
5. agrupe leituras relacionadas.
6. não resuma novamente informações já claras.
7. carregue políticas adicionais sob demanda.
8. preserve evidências e decisões essenciais.

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
