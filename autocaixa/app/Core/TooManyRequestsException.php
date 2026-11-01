<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Lançada quando um limite de requisições (rate limit) é excedido. */
final class TooManyRequestsException extends RuntimeException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct('Muitas requisições. Tente novamente em instantes.');
    }
}
