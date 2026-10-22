<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

/**
 * Contrato único para todos os provedores de pagamento. As regras de venda dependem só desta interface.
 * Adaptadores reais (Cielo Conecta para cartão, API Pix da Cielo para Pix) implementam esta mesma interface.
 */
interface PaymentGateway
{
    public function name(): string;

    /** Cria a cobrança no provedor e retorna o identificador externo. */
    public function createCharge(int $amountCents, string $method, string $reference): string;

    /** Consulta o estado real da cobrança no provedor. */
    public function status(string $externalId): string; // pending|approved|declined|unknown
}
