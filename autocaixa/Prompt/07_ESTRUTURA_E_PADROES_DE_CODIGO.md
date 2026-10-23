# Política de estrutura de projeto e padrões de código

## Objetivo
Manter estrutura previsível, estilo consistente e responsabilidades fáceis de localizar.

## Descubra antes de reorganizar
- Inspecione diretórios, naming, convenções, autoload, rotas, módulos e configuração.
- Reutilize os padrões estabelecidos se forem seguros e coerentes.
- Não crie uma estrutura nova por preferência pessoal quando já há uma estrutura funcional.
- Antes de mover arquivos, encontre todas as referências, imports, rotas, jobs, comandos e configurações relacionados.

## Convenções
- Siga formatador, linter, type checker e padrões já configurados.
- Use nomes que expressem o papel do módulo, função, variável e arquivo.
- Mantenha funções e classes com responsabilidade clara.
- Evite funções gigantes, duplicação e estado global mutável sem justificativa.
- Use tipos e contratos oferecidos pela linguagem/framework quando úteis.
- Use constantes/configuração para valores ambientais; não espalhe URLs, chaves ou IDs de ambiente no código.
- Comentários devem explicar intenção, restrição ou decisão não óbvia, não repetir literalmente o código.
- Remova código morto somente após verificar suas referências e o escopo do pedido.

## Projetos PHP
- Respeite PSR-12 e convenções PSR pertinentes quando o projeto as adotar ou quando não houver padrão conflitante justificado.
- Verifique versão de PHP declarada no `composer.json`, plataforma Composer e servidor real antes de usar sintaxe recente.
- Use namespaces e autoload Composer quando adequados à aplicação.
- Evite misturar consultas SQL, regra de negócio e HTML no mesmo bloco quando isso prejudicar manutenção ou segurança.
- Mantenha segredos e configuração fora do código-fonte.
- Não introduza framework ou dependência grande apenas para uma melhoria pequena.

## Compatibilidade
Antes de trocar nomes, diretórios, classes ou assinaturas, procure consumidores internos, integrações, scripts, testes, deploy e documentação. Planeje migração para breaking changes.

## Entrega
Explique alterações estruturais e por que são necessárias. Execute o formatador/linter disponível e verifique se a aplicação ainda carrega/builda quando possível.
