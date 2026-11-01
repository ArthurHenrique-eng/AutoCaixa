<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Resolve o IP real do cliente. Por padrão usa apenas REMOTE_ADDR.
 * X-Forwarded-For só é considerado quando REMOTE_ADDR está na lista de proxies
 * confiáveis (TRUSTED_PROXIES), para não permitir que o próprio cliente falsifique o IP.
 */
final class Ip
{
    /** @param list<string> $trustedProxies */
    public static function client(array $trustedProxies = []): string
    {
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if ($trustedProxies !== [] && in_array($remote, $trustedProxies, true)) {
            $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
            if ($forwarded !== '') {
                $first = trim(explode(',', $forwarded)[0]);
                if (filter_var($first, FILTER_VALIDATE_IP) !== false) {
                    return substr($first, 0, 45);
                }
            }
        }
        return substr($remote, 0, 45);
    }
}
