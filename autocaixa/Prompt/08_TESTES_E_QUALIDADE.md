# Política de testes e qualidade

## Objetivo
Usar testes para demonstrar o comportamento relevante e reduzir regressões; não tratar uma suíte verde como prova de ausência de vulnerabilidades.

## Estratégia
Escolha a camada de teste conforme o comportamento: unitário para regras isoladas, integração para banco/serviços, contrato para APIs, ponta a ponta para fluxos principais e testes de segurança para fronteiras de confiança.

## Regras
- Não apague nem enfraqueça testes para fazer a implementação passar.
- Para um bug corrigido, adicione um teste de regressão sempre que viável.
- Inclua casos de sucesso, falha, limites, dados inválidos, permissões insuficientes e estado concorrente quando pertinentes.
- Use fixtures/factories e dados de teste identificáveis; não use credenciais ou dados pessoais reais.
- Evite testes dependentes de ordem, horário local, rede pública ou estado compartilhado imprevisível.
- Não faça mocks tão amplos que removam o comportamento que o teste deveria verificar.
- Para mudanças em contratos ou schemas, verifique consumidores e compatibilidade.

## Segurança
Testar explicitamente autenticação e autorização negativas, IDOR, validação, abuso de limites, sessões expiradas, entradas maliciosas e integridade transacional conforme aplicável. Um teste que verifica apenas que a resposta é HTTP 200 não é suficiente para validar uma regra de acesso.

## Comandos e evidências
- Descubra os comandos reais em `composer.json`, package scripts, Makefile, CI ou documentação.
- Execute testes relevantes e relate resultado, quantidade e limitações observadas quando disponíveis.
- Não diga que todos os testes passaram se apenas parte da suíte foi executada.
- Diferencie “não executado”, “falhou por ambiente” e “falhou por defeito do código”.
- Não altere ambiente de produção para testar sem autorização.

## Critérios de aceite
Defina resultados observáveis em termos do comportamento esperado. Ao terminar, revise diff, mensagens de erro, logs, testes e possível impacto em funcionalidades existentes.
