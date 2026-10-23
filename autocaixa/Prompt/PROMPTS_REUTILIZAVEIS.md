# Prompts operacionais reutilizáveis

Copie e adapte um dos modelos abaixo. Mencione os documentos de política pertinentes; não precisa pedir que a IA leia todas as políticas para toda tarefa.

## 1. Mapear o repositório sem modificar

> Faça uma inspeção somente leitura do repositório. Leia `AGENTS.md` e `PROJECT_CONTEXT.md`, se existir. Mapeie stack, estrutura, pontos de entrada, comandos reais de teste/build, arquitetura, dados, integrações e riscos conhecidos. Não modifique arquivos, não instale dependências e não execute operações destrutivas. Separe fatos verificados de hipóteses. Entregue um mapa breve, pontos críticos e lacunas que precisam ser confirmadas.

## 2. Auditoria de segurança

> Audite o projeto seguindo `02_SEGURANCA_PHP.md` quando for PHP e as políticas relevantes de APIs, banco e deploy. Inspecione o código; não se limite a uma checklist genérica. Para cada achado, informe severidade, evidência (arquivo/linha ou fluxo), cenário de exploração, impacto, correção recomendada e teste de regressão. Distinga vulnerabilidade confirmada de risco potencial. Não altere o código sem autorização para remediação. Não declare que o projeto é totalmente seguro.

## 3. Corrigir um bug

> Investigue primeiro o bug e o fluxo real. Leia as políticas relevantes. Reproduza ou escreva um teste de regressão antes da correção quando possível. Encontre a causa raiz, implemente a menor mudança segura e execute os testes relacionados. Não refatore áreas não relacionadas. Informe evidências de reprodução, arquivos alterados, testes e limitações.

## 4. Implementar uma funcionalidade

> Primeiro descreva como o projeto implementa funcionalidades semelhantes e quais arquivos/contratos serão afetados. Defina critérios de aceite observáveis, leia as políticas pertinentes e proponha uma implementação compatível com a arquitetura atual. Depois implemente de forma incremental, inclua validação, autorização, tratamento de erro, acessibilidade e testes conforme aplicável. Preserve contratos e funcionalidades existentes. Não publique nem faça commit sem autorização específica.

## 5. Revisar interface sem redesign amplo

> Revise a interface existente e identifique problemas concretos de hierarquia, responsividade, acessibilidade e consistência. Preserve identidade e componentes existentes. Faça apenas mudanças relevantes ao objetivo indicado. Verifique estados de loading, vazio, sucesso, erro, foco e teclado quando aplicável. Não alegue inspeção visual se não executou a aplicação em um navegador.

## 6. Alteração de banco de dados

> Leia `09_BANCO_DE_DADOS_E_MIGRACOES.md`. Inspecione schema, migrations e consumidores. Apresente risco de perda de dados, compatibilidade com versões em execução, plano de migração/rollback e testes. Não rode comandos destrutivos ou migrações em produção sem aprovação explícita.

## 7. Revisão antes de release

> Faça uma revisão somente leitura do diff e do estado do projeto. Confira testes, build, configuração, segredos, migrations, logs, autenticação/autorização, compatibilidade, documentação e plano de rollback conforme aplicável. Não faça commit, push ou deploy. Liste bloqueadores, recomendações e evidências observadas.

## 8. Atualizar documentação

> Compare documentação com código e configurações reais. Atualize somente os documentos responsáveis pelas informações afetadas. Não invente comandos, recursos ou resultados. Preserve decisões anteriores ainda válidas, remova contradições e indique o que permanece não confirmado.
