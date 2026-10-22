<?php
declare(strict_types=1);

namespace App\Core;

/** Token CSRF por sessão, validado em todas as requisições POST da API. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function validate(?string $header): bool
    {
        return isset($_SESSION['csrf']) && $header !== null && hash_equals($_SESSION['csrf'], $header);
    }
}
