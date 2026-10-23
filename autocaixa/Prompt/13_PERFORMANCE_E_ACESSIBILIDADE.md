# Política de performance e acessibilidade

## Performance
- Meça ou identifique evidência antes de otimizar; não assuma que uma mudança é mais rápida.
- Priorize gargalos do fluxo principal e consultas observadas.
- Avalie N+1, índices, payload, caching, imagens, bundle, renderização e chamadas externas conforme a stack.
- Não introduza cache sem decidir chave, expiração, invalidação, privacidade e risco de dados cruzados.
- Não permita que otimizações ignorem autorização, consistência ou validação.
- Defina limites de corpo, tempo, paginação e processamento para entradas custosas.
- Evite otimização prematura e dependências pesadas sem benefício comprovado.

## Acessibilidade
- Use HTML semântico e nomes acessíveis para controles.
- Garanta navegação por teclado, ordem de foco lógica e foco visível.
- Associe labels, instruções e erros aos campos.
- Não comunique estados apenas por cor.
- Preserve contraste e ampliação de texto.
- Respeite `prefers-reduced-motion` quando apropriado.
- Ofereça alternativas textuais para conteúdo visual relevante.
- Implemente acessibilidade em modais, menus, mensagens, carregamento e atualizações dinâmicas.

## Validação
Use critérios WCAG atuais e ferramentas disponíveis como apoio, não como prova automática de conformidade. Testes automatizados devem ser complementados por revisão manual e teclado quando possível. Não alegue conformidade total sem avaliação suficiente.

## Não regressão
Ao mudar layout ou performance, avalie dispositivos, browsers e fluxos relevantes. Compare métricas antes/depois quando houver instrumentos disponíveis e relate limitações.
