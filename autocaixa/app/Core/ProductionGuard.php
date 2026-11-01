<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Impede que o driver de pagamento simulado seja usado em produção.
 * Falha de forma explícita na inicialização (fail closed): nunca cai
 * silenciosamente para outro driver nem deixa o erro passar despercebido.
 */
final class ProductionGuard
{
    public static function isProduction(string $appEnv): bool
    {
        return strtolower(trim($appEnv)) === 'production';
    }

    /** @throws RuntimeException quando a combinação não é permitida. */
    public static function assertPaymentDriverAllowed(string $appEnv, string $paymentDriver): void
    {
        if (self::isProduction($appEnv) && $paymentDriver === 'simulated') {
            throw new RuntimeException(
                'Configuração inválida: PAYMENT_DRIVER=simulated não é permitido com APP_ENV=production. '
                . 'Configure um driver de pagamento real antes de publicar.'
            );
        }
    }
}
