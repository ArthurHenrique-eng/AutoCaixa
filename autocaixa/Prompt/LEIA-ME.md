# Pacote de instruções para agentes de IA em projetos de software

Este pacote reúne um conjunto modular de instruções reutilizáveis. Ele foi pensado para projetos PHP, mas as regras universais podem ser adaptadas a outras stacks.

## Como instalar

1. Faça backup ou confira o estado do Git antes de copiar os arquivos.
2. Copie `AGENTS.md` para a raiz do repositório.
3. Se usar Claude Code, copie também `CLAUDE.md` para a raiz.
4. Se usar Cursor, copie a pasta `.cursor/rules/` para a raiz do projeto.
5. Copie os documentos de políticas que fazem sentido para o projeto. É melhor manter só as políticas relevantes e atualizadas do que acumular muitos arquivos contraditórios.
6. Preencha `PROJECT_CONTEXT.md` com fatos reais do projeto. Não deixe exemplos como se fossem fatos.
7. Para o AutoCaixa, use também `AUTOCAIXA_REGRAS_ESPECIFICAS.md`. **Não** copie essa política específica para aplicações que não sejam o AutoCaixa.

## O que faz a IA realmente ler os arquivos?

Os arquivos `.md` soltos na raiz não são necessariamente carregados automaticamente por todo agente. `AGENTS.md` é reconhecido pelo Codex e também pode ser usado como instrução de projeto pelo Cursor. Claude Code usa `CLAUDE.md`. O pacote inclui adaptadores pequenos para encaminhar o agente aos documentos relevantes. Mesmo assim, confira como a ferramenta escolhida aplica instruções no seu modo de uso.

As referências às políticas abaixo orientam o agente a abrir o documento relacionado à tarefa. Isso evita exigir a leitura de todos os arquivos a cada alteração simples, o que aumenta o contexto e pode prejudicar a eficiência.

## Mapa dos documentos

| Arquivo | Use quando |
|---|---|
| `AGENTS.md` | Instruções centrais reconhecidas por agentes compatíveis |
| `CLAUDE.md` | Instruções de raiz para Claude Code |
| `PROJECT_CONTEXT.md` | Registrar objetivo, stack, comandos, ambientes, regras e decisões reais |
| `01_PROMPT_MESTRE_UNIVERSAL.md` | Engenharia, análise ou implementação geral |
| `02_SEGURANCA_PHP.md` | Segurança em aplicações PHP; contém o prompt de segurança PHP já consolidado |
| `03_LEGISLACAO_E_CONFORMIDADE.md` | Requisitos legais e regulatórios relevantes ao produto |
| `04_PRIVACIDADE_COOKIES_E_CONSENTIMENTO.md` | Cookies, rastreadores, consentimento e privacidade |
| `05_DESIGN_UI_UX.md` | Interface, experiência, consistência visual e responsividade |
| `06_ARQUITETURA_LIMPA.md` | Limites de camadas, dependências e regras de negócio |
| `07_ESTRUTURA_E_PADROES_DE_CODIGO.md` | Organização de arquivos, convenções e padrões de implementação |
| `08_TESTES_E_QUALIDADE.md` | Testes, revisão, regressões e critérios de conclusão |
| `09_BANCO_DE_DADOS_E_MIGRACOES.md` | Schema, consultas, integridade, migrations e transações |
| `10_APIS_E_INTEGRACOES.md` | APIs, contratos, serviços externos e tratamento de falhas |
| `11_DOCUMENTACAO.md` | README, documentação técnica e manutenção de documentos |
| `12_DEPLOY_INFRA_E_CICD.md` | Ambientes, CI/CD, configuração e publicação |
| `13_PERFORMANCE_E_ACESSIBILIDADE.md` | Desempenho, acessibilidade e usabilidade inclusiva |
| `14_PAGAMENTOS_E_OPERACOES_CRITICAS.md` | Dinheiro, estoque, pedidos, idempotência e consistência |
| `15_WORKFLOW_GIT_E_MUDANCAS.md` | Segurança de mudanças, branches, commits e deploys |
| `PROMPTS_REUTILIZAVEIS.md` | Prompts operacionais para auditoria, bugs, features e revisão |
| `AUTOCAIXA_REGRAS_ESPECIFICAS.md` | Regras exclusivas para o AutoCaixa, quando aplicáveis |

## Princípios de uso

- Não trate nenhum documento como prova de que o código já está seguro ou em conformidade.
- Inspecione o repositório antes de concluir algo sobre ele.
- Resolva conflitos entre documentos priorizando requisitos legais confirmados, segurança, instruções específicas do projeto e decisões já aprovadas. Se o conflito for relevante, informe-o em vez de escolher silenciosamente.
- Não transforme todas as sugestões em requisitos automáticos: aplique o que for pertinente à stack, ao risco e ao escopo.
- Legislação e normas podem mudar. Verifique fontes oficiais atualizadas e a jurisdição antes de afirmar obrigações legais.
- Nenhum agente deve afirmar que executou testes, publicou um release ou validou segurança sem evidências observáveis.
