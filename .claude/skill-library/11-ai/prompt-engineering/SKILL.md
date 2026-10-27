---
name: prompt-engineering
description: Cria instruções de agente claras, testáveis e econômicas. Use para CLAUDE.md, SKILL.md, prompts e workflows.
---

# prompt-engineering

## Objetivo e acionamento
Cria instruções de agente claras, testáveis e econômicas. Use para CLAUDE.md, SKILL.md, prompts e workflows.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/01_PROMPT_MESTRE_UNIVERSAL.md e autocaixa/Prompt/02_SEGURANCA_PHP.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Separe papel, contexto, restrições, passos e saída.
2. escreva descrições acionáveis.
3. não duplique políticas.
4. inclua exemplos úteis.
5. limite contexto permanente.
6. defina tratamento de incerteza e conflitos.

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
