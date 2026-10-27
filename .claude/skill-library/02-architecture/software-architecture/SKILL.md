---
name: software-architecture
description: Analisa limites, dependências e trade-offs da arquitetura existente. Use ao criar módulos, novas integrações ou mudanças transversais.
---

# software-architecture

## Objetivo e acionamento
Analisa limites, dependências e trade-offs da arquitetura existente. Use ao criar módulos, novas integrações ou mudanças transversais.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/06_ARQUITETURA_LIMPA.md e autocaixa/Prompt/07_ESTRUTURA_E_PADROES_DE_CODIGO.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Desenhe o fluxo real e as dependências.
2. mantenha regras de domínio separadas de HTTP, SQL e templates.
3. justifique cada camada nova.
4. prefira arquitetura evolutiva.
5. considere testabilidade, simplicidade e operação.
6. registre decisões importantes.

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
