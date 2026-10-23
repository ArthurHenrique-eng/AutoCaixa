# Política de deploy, infraestrutura e CI/CD

## Ambientes
- Separe desenvolvimento, teste/homologação e produção quando aplicável.
- Mantenha configurações por ambiente fora do código versionado.
- Use segredos fornecidos pelo ambiente/secret manager; nunca registre valores em logs ou exemplos.
- Evite permissões administrativas para processos de runtime.
- Documente dependências e requisitos reais de sistema.

## Pipeline
Conforme a stack, considere testes, lint, análise estática, build, auditoria de dependências, varredura de segredos, testes de segurança e validação de migrations. O pipeline deve falhar quando uma verificação crítica falhar, sem atalhos silenciosos.

## Deploy
- Avalie compatibilidade da nova versão com dados e versões em execução.
- Defina verificações pós-deploy e critério de rollback.
- Considere migrations expansivas/contrativas quando houver zero downtime.
- Não execute migrations destrutivas sem backup, plano de recuperação e autorização específica.
- Não publique automaticamente só porque os testes locais passaram.
- Não desative TLS, validações, CSP ou controles de acesso para contornar falhas.

## Infraestrutura
- Restrinja portas e serviços expostos.
- Mantenha runtime, sistema operacional e dependências corrigidos.
- Use health checks que não exponham dados internos.
- Configure logs, métricas e alertas com cuidado para evitar dados sensíveis.
- Verifique permissões de arquivos, diretórios públicos, backups e arquivos de configuração.
- Em PHP, confirme versão do servidor e configuração de produção; desative exposição de erros ao cliente e direcione logs para canal protegido.

## Mudanças externas
Commit, push, merge, release, deploy, alteração de DNS, variáveis de produção, recursos em cloud ou banco remoto exigem autorização explícita para essa ação específica. Antes da execução, informe alvo, impacto e reversibilidade quando relevante.

## Resultado
Informe ambiente-alvo, versão, checks realizados, resultado observado, riscos e eventual plano de rollback. Não diga que publicou ou verificou produção sem evidência real.
