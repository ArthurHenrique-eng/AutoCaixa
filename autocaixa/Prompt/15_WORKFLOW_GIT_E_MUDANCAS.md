# Política de Git, alterações e operações reversíveis

## Antes de editar
- Confira branch, `git status`, diffs e arquivos não rastreados relevantes.
- Não descarte, sobrescreva ou “limpe” mudanças preexistentes do usuário.
- Preserve nomes de branch e convenções documentadas no projeto.
- Faça alterações em escopo claro e revise o diff antes de concluir.

## Git
Não execute sem autorização explícita para a ação específica:
- commit;
- push ou force push;
- merge ou rebase de branches compartilhadas;
- exclusão/criação de branches que altere o fluxo de trabalho do usuário;
- reset, checkout destrutivo ou limpeza de arquivos não rastreados;
- publicação de release.

Comandos somente de leitura podem ser executados normalmente. Antes de ação potencialmente destrutiva, explique o alvo, o impacto e se há caminho de recuperação.

## Escopo
- Não altere arquivos não relacionados para aproveitar a tarefa.
- Não renomeie, mova ou exclua arquivos sem verificar referências.
- Não adicione arquivos de configuração, dependências ou automações só por conveniência.
- Não faça commits de segredos, artefatos gerados, logs ou dados reais.
- Não desative hooks, linters, testes ou verificações de segurança para obter sucesso artificial.

## Mudanças em sistemas externos
Banco remoto, hospedagem, cloud, DNS, billing, contas, integrações, deploy e dados de produção são operações diferentes da edição local. Exigem consentimento explícito para o alvo e ação específicos. Nunca infira autorização para publicar porque foi pedido para corrigir código.

## Entrega
Informe arquivos alterados e comandos usados. Se a tarefa não pediu commit/push/deploy, deixe essas ações pendentes. Se detectar alterações não relacionadas, preserve-as e avise.
