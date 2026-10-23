# Política de Design, UI e UX

## Objetivo
Criar interfaces claras e consistentes com o produto, mantendo os padrões existentes quando não houver autorização explícita para redesign.

## Antes de alterar
- Inspecione as telas, componentes, estilos, tokens, fontes, ícones e responsividade atuais.
- Identifique o sistema de design e os componentes reutilizáveis já existentes.
- Entenda o público, tarefa principal, hierarquia de conteúdo e estados da interface.
- Preserve identidade visual, rotas, comportamento e linguagem estabelecidos salvo pedido contrário.
- Não invente requisitos de marca. Se estiver faltando uma diretriz, proponha uma opção coerente e identifique-a como proposta.

## Princípios de interface
- Hierarquia visual clara, espaçamento consistente e leitura confortável.
- Contraste suficiente, estados de foco visíveis e navegação por teclado.
- Componentes com estados completos: padrão, hover, foco, ativo, loading, vazio, sucesso, erro, desabilitado e sem permissão quando aplicável.
- Feedback próximo da ação e mensagens de erro que expliquem como corrigir sem expor detalhes internos.
- Formulários com labels, validação compreensível e preservação dos dados digitados quando seguro.
- Layout responsivo e utilizável em resoluções reais, não apenas em uma captura de tela.
- Evite excesso de gradientes, cartões, sombras, animações ou elementos decorativos sem função.
- Evite criar aparência genérica de template. A estética deve refletir propósito, público e marca.
- Não usar emojis, ilustrações ou ícones decorativos de forma indiscriminada em interfaces profissionais.

## Consistência
- Reutilize tokens de cores, tipografia, espaçamento, radius, elevação e breakpoints existentes.
- Reutilize componentes e padrões antes de criar duplicatas.
- Não espalhe valores arbitrários quando houver tokens.
- Não introduza uma nova biblioteca de componentes ou ícones sem necessidade e análise de impacto.

## Mudanças seguras
- Faça mudanças focadas no pedido.
- Não refaça a página inteira para ajustar um componente.
- Preserve integrações, testes, validações e acessibilidade.
- Não esconda elementos importantes para contornar falta de espaço; reprojete a hierarquia responsivamente.
- Não implemente interações visualmente sugeridas que não funcionam.

## Validação visual
Quando ferramentas disponíveis permitirem, valide em telas pequenas e grandes, compare antes/depois e teste os estados importantes. Confira console, erros de rede e comportamento de teclado. Se não houver navegador ou captura de tela disponível, informe essa limitação em vez de alegar validação visual.
