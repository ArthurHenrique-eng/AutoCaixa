# Política de APIs e integrações externas

## Contratos
- Identifique contratos existentes, versionamento, autenticação, consumidores e limites de compatibilidade antes de alterar endpoints.
- Valide entrada no servidor e valide respostas de fornecedores externos.
- Retorne apenas os dados necessários e use códigos HTTP coerentes.
- Padronize erros sem expor stack traces, SQL, tokens, segredos ou detalhes internos.
- Documente schemas, autenticação, permissões, limites, erros e efeitos colaterais relevantes.

## Confiança e segurança
- Trate requisições, headers, arquivos e respostas externas como entrada não confiável.
- Use TLS e valide certificados.
- Proteja chaves de API, refresh tokens e credenciais por secrets/environment management; nunca inclua segredos em URLs, frontend ou logs.
- Configure timeout, limites de tamanho, limites de redirects e retries.
- Avalie SSRF quando o servidor busca URLs fornecidas por usuários.
- Restrinja CORS e origens conforme o uso real.
- Use autenticação e autorização por recurso, não apenas por endpoint.
- Não confie em IDs, roles ou valores de preço enviados pelo cliente.

## Resiliência
- Defina timeouts explícitos.
- Use retries apenas para falhas transitórias e operações seguras; aplique backoff e limites.
- Para efeitos não idempotentes, use chave de idempotência ou mecanismo equivalente quando o fornecedor oferecer suporte.
- Defina comportamento para timeout após possível sucesso, resposta inválida, indisponibilidade e rate limit.
- Evite loops de retry que ampliem uma falha.
- Quando há filas/webhooks, considere duplicação, reordenação e atraso.

## Webhooks
- Verifique a assinatura conforme a especificação oficial do fornecedor.
- Valide timestamp/nonce quando suportado para mitigar replay.
- Compare identificadores de evento e processe de forma idempotente.
- Não confie em status enviado pelo navegador para confirmar pagamento ou efeito crítico.
- Responda conforme a janela e os requisitos documentados pelo fornecedor.
- Armazene evidências operacionais com proteção adequada, sem guardar segredos desnecessários.

## Integrações
Use documentação oficial e atual para endpoints, versões, assinatura, códigos e limites. Não invente APIs ou campos. Se não houver acesso à rede ou credenciais, deixe claro que a integração não foi testada de ponta a ponta.
