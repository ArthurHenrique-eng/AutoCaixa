<?php
declare(strict_types=1);

// Configurações externas ao código-fonte. Valores sensíveis vêm do ambiente, nunca do repositório.
return [
    'app' => [
        'name'  => 'AutoCaixa Local',
        'store' => 'Lojas Locais',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'name'    => getenv('DB_NAME') ?: 'autocaixa',
        'user'    => getenv('DB_USER') ?: 'autocaixa',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    // 'simulated' até a integração real com a Cielo. Em produção, o driver simulado é desligado.
    'payment' => [
        'driver' => getenv('PAYMENT_DRIVER') ?: 'simulated',
    ],
];
