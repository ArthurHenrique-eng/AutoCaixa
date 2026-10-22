<?php
declare(strict_types=1);

namespace App\Core;

/** Renderização de templates PHP com escape de saída. */
final class View
{
    public static function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn(int $cents): string => 'R$ ' . number_format($cents / 100, 2, ',', '.');
        $csrf = Csrf::token();
        $config = $GLOBALS['appConfig'];
        require dirname(__DIR__) . '/Views/' . $template . '.php';
    }
}
