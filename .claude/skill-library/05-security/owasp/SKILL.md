---
name: owasp
description: Usa riscos OWASP como checklist proporcional ao contexto. Use em features novas, auditorias, APIs, autenticação e integrações.
---

# owasp

## Objetivo e acionamento
Usa riscos OWASP como checklist proporcional ao contexto. Use em features novas, auditorias, APIs, autenticação e integrações.

## Contexto e fontes
1. Respeite CLAUDE.md e AGENTS.md da raiz.
2. Leia autocaixa/Prompt/PROJECT_CONTEXT.md e consulte autocaixa/Prompt/AGENTS.md para selecionar políticas.
3. Política primária: autocaixa/Prompt/02_SEGURANCA_PHP.md e autocaixa/Prompt/AUTOCAIXA_REGRAS_ESPECIFICAS.md.
4. Abra políticas adicionais somente se o escopo cruzar outros domínios. Não leia todas.

## Procedimento
1. Avalie controle de acesso, criptografia, injeção, design inseguro, configuração, componentes vulneráveis, autenticação, integridade de software/dados, logs e SSRF se aplicável.
2. associe riscos a evidência.
3. não alegue certificação ou cobertura total.

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
