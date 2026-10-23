# Política de Arquitetura Limpa e limites de responsabilidade

## Objetivo
Manter o código organizado por responsabilidades, sem forçar um padrão arquitetural que não combine com o tamanho ou com a stack do projeto.

## Descoberta
Antes de propor refatoração, mapeie os pontos de entrada, módulos, dependências, fluxo de dados e regras de negócio. Identifique a arquitetura existente e seus motivos quando houver documentação.

## Regras de dependência
- A lógica de domínio não deve depender desnecessariamente de detalhes de interface, framework, banco ou fornecedor externo.
- Controllers/handlers devem coordenar requisições e respostas, não concentrar toda a regra de negócio.
- Regras de negócio importantes devem ter um local claro e testável.
- Acesso a persistência e integrações deve estar atrás de interfaces ou módulos apropriados quando isso trouxer benefício concreto.
- Validação de entrada e regras de domínio são diferentes: validar formato não substitui verificar invariantes do negócio.
- Evite ciclos de dependência e imports que tornem módulos inseparáveis.
- Não criar interfaces, factories, repositories ou camadas apenas por ritual. Use abstrações quando houver motivo de teste, isolamento, reutilização ou evolução.

## Separação de responsabilidades
Considere, conforme a stack, camadas de apresentação/entrada, aplicação/casos de uso, domínio, persistência e infraestrutura. Os nomes e a quantidade de camadas devem acompanhar a complexidade real.

## Integridade do domínio
- Centralize regras críticas para evitar versões conflitantes da mesma regra.
- Explicite invariantes e estados válidos.
- Não permita que a interface ou uma API alternativa burle regras do domínio.
- Trate concorrência, transações e idempotência onde o domínio exigir.

## Refatoração
- Não altere comportamento silenciosamente.
- Estabeleça testes de caracterização antes de mudanças arriscadas.
- Refatore incrementalmente com diffs revisáveis.
- Não mova dezenas de arquivos sem ganho demonstrável.
- Atualize imports, configurações, testes e documentação relacionados.

## Critérios de revisão
Avalie coesão, acoplamento, dependências, duplicação, testabilidade e facilidade de localizar regras. Prefira simplicidade explícita a abstrações especulativas. Registre decisões arquiteturais relevantes e suas alternativas quando necessário.
