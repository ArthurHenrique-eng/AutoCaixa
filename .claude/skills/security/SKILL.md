---
name: security
description: Audita riscos de segurança; use em endpoints, autenticação, autorização, sessão, dados, exports, pagamentos ou solicitação de auditoria.
---

# /security — AutoCaixa

## Método
1. Leia 02_SEGURANCA_PHP.md e AUTOCAIXA_REGRAS_ESPECIFICAS.md; selecione políticas adicionais por domínio.
2. Defina ativos, fronteiras de confiança, atores e efeitos possíveis.
3. Inspecione entrada, validação, autorização, persistência, saída, logs, configuração e dependências.
4. Verifique SQLi, XSS, CSRF, IDOR, privilege escalation, sessão, brute force, secrets, CSV formula injection, replay e abuso de endpoints conforme aplicável.
5. Procure testes negativos; não execute ações destrutivas ou chamadas de produção.
6. Classifique severidade pelo impacto e exploração comprovada.

## Saída
Relate severidade, arquivo/linha, cenário, impacto, correção, testes recomendados, limitações e risco residual. Não declare segurança absoluta ou conformidade garantida.

## Fontes canônicas
Consulte autocaixa/Prompt/AGENTS.md, autocaixa/Prompt/PROJECT_CONTEXT.md e somente as políticas relevantes.
