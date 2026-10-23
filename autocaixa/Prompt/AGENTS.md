# Instruções do repositório para agentes de IA

## Fonte de verdade

Antes de trabalhar, leia este arquivo e identifique `PROJECT_CONTEXT.md`, se existir. Abra apenas as políticas adicionais relevantes à tarefa. Não carregue todos os documentos indiscriminadamente para uma mudança pequena.

- Engenharia geral: `01_PROMPT_MESTRE_UNIVERSAL.md`
- Segurança em PHP: `02_SEGURANCA_PHP.md`
- Legislação/conformidade: `03_LEGISLACAO_E_CONFORMIDADE.md`
- Privacidade, cookies e consentimento: `04_PRIVACIDADE_COOKIES_E_CONSENTIMENTO.md`
- Interface: `05_DESIGN_UI_UX.md`
- Arquitetura: `06_ARQUITETURA_LIMPA.md`
- Estrutura e estilo: `07_ESTRUTURA_E_PADROES_DE_CODIGO.md`
- Testes: `08_TESTES_E_QUALIDADE.md`
- Banco de dados: `09_BANCO_DE_DADOS_E_MIGRACOES.md`
- APIs: `10_APIS_E_INTEGRACOES.md`
- Documentação: `11_DOCUMENTACAO.md`
- Infra/CI/CD: `12_DEPLOY_INFRA_E_CICD.md`
- Performance/acessibilidade: `13_PERFORMANCE_E_ACESSIBILIDADE.md`
- Pagamentos/estoque/ações críticas: `14_PAGAMENTOS_E_OPERACOES_CRITICAS.md`
- Git e alterações arriscadas: `15_WORKFLOW_GIT_E_MUDANCAS.md`
- AutoCaixa, somente se este for o projeto: `AUTOCAIXA_REGRAS_ESPECIFICAS.md`

## Regras de trabalho

1. Inspecione os arquivos relevantes e o estado atual do Git antes de editar.
2. Baseie afirmações em código, testes, documentação ou resultados observados. Marque hipóteses como hipóteses.
3. Preserve funcionalidades, contratos, decisões arquiteturais e trabalho não relacionado do usuário.
4. Faça a menor mudança que resolva o problema sem comprometer segurança ou qualidade.
5. Não introduza dependências, abstrações ou arquivos desnecessários.
6. Execute as verificações pertinentes à mudança. Registre quais comandos foram executados e os resultados reais. Se algo não puder ser executado, declare a limitação.
7. Não remova testes, reduza controles de segurança ou enfraqueça a validação apenas para obter build verde.
8. Não exponha segredos em código, logs, testes, exemplos, commits ou respostas.
9. Não realize ações externas ou difíceis de reverter — como commit, push, publicação, alteração de infraestrutura, exclusão de branch ou migração destrutiva — sem autorização explícita para a ação específica.
10. Não afirme que uma revisão garante segurança absoluta ou conformidade legal.

## Fluxo padrão

- Entenda pedido, stack e comportamento esperado.
- Identifique escopo, arquivos afetados, dependências e risco.
- Para mudanças relevantes, apresente brevemente o plano antes da implementação quando a interação permitir.
- Implemente de forma incremental.
- Teste o comportamento modificado e regressões pertinentes.
- Revise diff, segurança, compatibilidade e documentação.
- Entregue resumo, arquivos alterados, testes e limitações.

## Prioridade em conflitos

Nunca sacrifique segurança, integridade dos dados ou requisito legal confirmado para preservar conveniência. Caso instruções se contradigam, não improvise silenciosamente: explique o conflito e siga a alternativa mais segura que não invente requisitos.
