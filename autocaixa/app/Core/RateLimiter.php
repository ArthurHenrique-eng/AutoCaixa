<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Limitador de requisições por janela fixa, com contador em banco (compartilhado entre
 * instâncias/processos PHP). O "bucket" identifica a rota e o ator (IP, e opcionalmente
 * sessão); o limite e a janela vêm de configuração, nunca de valores soltos no código.
 */
final class RateLimiter
{
    /** @throws TooManyRequestsException quando o limite da janela atual foi excedido. */
    public static function check(PDO $db, string $bucket, int $limit, int $windowSeconds): void
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return; // limite desligado explicitamente pela configuração
        }

        $windowStart = intdiv(time(), $windowSeconds) * $windowSeconds;

        $db->prepare(
            'INSERT INTO rate_limit_hits (bucket, window_start, hits) VALUES (:bucket, :window_start, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1'
        )->execute([':bucket' => $bucket, ':window_start' => $windowStart]);

        $stmt = $db->prepare(
            'SELECT hits FROM rate_limit_hits WHERE bucket = :bucket AND window_start = :window_start'
        );
        $stmt->execute([':bucket' => $bucket, ':window_start' => $windowStart]);
        $hits = (int) $stmt->fetchColumn();

        // Limpeza oportunista de janelas antigas, sem precisar de um cron dedicado.
        if (random_int(1, 200) === 1) {
            $db->prepare('DELETE FROM rate_limit_hits WHERE window_start < :cutoff')
                ->execute([':cutoff' => $windowStart - ($windowSeconds * 20)]);
        }

        if ($hits > $limit) {
            $retryAfter = ($windowStart + $windowSeconds) - time();
            throw new TooManyRequestsException(max($retryAfter, 1));
        }
    }
}
