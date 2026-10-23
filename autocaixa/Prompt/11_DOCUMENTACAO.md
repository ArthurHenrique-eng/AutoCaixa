# Política de documentação técnica

## Objetivo
Manter documentação útil, curta o suficiente para ser usada e alinhada ao código real.

## O que documentar
Conforme a complexidade do projeto, mantenha informações sobre:
- propósito, escopo e principais fluxos;
- stack e versões suportadas;
- instalação e configuração;
- variáveis de ambiente pelo nome e finalidade, nunca pelo valor secreto;
- comandos de execução, testes, lint, build e migrations;
- arquitetura e fronteiras de módulos;
- APIs, autenticação e autorização;
- modelo de dados e decisões relevantes;
- deploy, operação, observabilidade e recuperação;
- riscos conhecidos e limitações.

## Regras
- Inspecione o código e a documentação atual antes de escrever.
- Não invente comandos. Execute-os ou confirme-os em configuração/fonte confiável do próprio repositório.
- Atualize o documento existente responsável pelo assunto antes de criar outro arquivo redundante.
- Não replique a mesma informação em vários lugares; use links/referências internas.
- Separe instruções para usuário, manutenção técnica, operação e decisões arquiteturais.
- Marque claramente exemplos, placeholders e requisitos ainda não confirmados.
- Não coloque dados reais, tokens, senhas, hosts privados ou dumps sensíveis em documentação pública.
- Não declare funcionalidade planejada como se já estivesse implementada.

## Diagramas e decisões
Use diagramas simples para fluxos com valor real. Registre decisões importantes com contexto, opções consideradas, decisão e consequências; não crie ADR para toda alteração trivial.

## Revisão
Após alterações de arquitetura, ambiente, comandos, API, banco ou comportamento operacional, confira se a documentação relacionada ficou coerente com a implementação. Remova instruções obsoletas quando isso for seguro e rastreável.
