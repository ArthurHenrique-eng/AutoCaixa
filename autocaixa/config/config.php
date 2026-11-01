<?php
declare(strict_types=1);

// Configurações externas ao código-fonte. Valores sensíveis vêm do ambiente, nunca do repositório.
return [
    'app' => [
        'name'  => 'AutoCaixa Local',
        'store' => 'Lojas Locais',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        // 'local' em desenvolvimento/testes; 'production' na loja. Controla o bloqueio
        // do pagamento simulado em ProductionGuard — nunca usado para outra coisa.
        'env'   => strtolower(trim((string) (getenv('APP_ENV') ?: 'local'))),
    ],
    'db' => [
        // Credenciais de runtime: usadas pela aplicação a cada requisição.
        // Devem ter só SELECT, INSERT, UPDATE, DELETE — sem privilégios de DDL (ver docs/hospedagem.md).
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'name'    => getenv('DB_NAME') ?: 'autocaixa',
        'user'    => getenv('DB_USER') ?: 'autocaixa',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'db_migration' => [
        // Credenciais usadas só por scripts/migrate.php, com privilégio de alterar schema (DDL).
        // Se DB_MIGRATION_USER/DB_MIGRATION_PASS não forem definidas, cai para as credenciais de
        // runtime (comportamento anterior), para não quebrar instalações já existentes.
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'name'    => getenv('DB_NAME') ?: 'autocaixa',
        'user'    => getenv('DB_MIGRATION_USER') ?: (getenv('DB_USER') ?: 'autocaixa'),
        'pass'    => getenv('DB_MIGRATION_PASS') ?: (getenv('DB_PASS') ?: ''),
        'charset' => 'utf8mb4',
    ],
    // 'simulated' até a integração real com a Cielo. Em produção, o driver simulado é desligado
    // (ProductionGuard recusa a inicialização quando app.env=production e driver=simulated).
    'payment' => [
        'driver' => getenv('PAYMENT_DRIVER') ?: 'simulated',
    ],
    // Limites de requisições por janela fixa (ver App\Core\RateLimiter). "window" em segundos.
    // Os *_WINDOW só existem para permitir testes automatizados com janelas curtas;
    // em produção, use os padrões (60s) e ajuste apenas os limites.
    'rate_limits' => [
        'login'    => [
            'limit'  => (int) (getenv('RATE_LIMIT_LOGIN') ?: 10),
            'window' => (int) (getenv('RATE_LIMIT_LOGIN_WINDOW') ?: 60),
        ],
        'cart'     => [
            'limit'  => (int) (getenv('RATE_LIMIT_CART') ?: 120),
            'window' => (int) (getenv('RATE_LIMIT_CART_WINDOW') ?: 60),
        ],
        'checkout' => [
            'limit'  => (int) (getenv('RATE_LIMIT_CHECKOUT') ?: 20),
            'window' => (int) (getenv('RATE_LIMIT_CHECKOUT_WINDOW') ?: 60),
        ],
        'public'   => [
            'limit'  => (int) (getenv('RATE_LIMIT_PUBLIC') ?: 300),
            'window' => (int) (getenv('RATE_LIMIT_PUBLIC_WINDOW') ?: 60),
        ],
    ],
    // IPs de proxy/load balancer confiáveis, separados por vírgula. Vazio por padrão: o X-Forwarded-For
    // só é usado quando REMOTE_ADDR bate com um destes IPs, para o cliente não poder falsificar o próprio IP.
    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) getenv('TRUSTED_PROXIES'))
    ))),
];
