<?php
declare(strict_types=1);

namespace App\Integrations\Payments;

use PDO;
use RuntimeException;

/**
 * SIMULAÇÃO. Não realiza cobrança real.
 * Existe apenas para a Fase 1 (protótipo local) e é identificado como simulado em toda a interface.
 * Não deve ser usado em produção: o config bloqueia este driver quando APP_ENV é produção.
 */
final class SimulatedPaymentGateway implements PaymentGateway
{
    public function __construct(private PDO $db) {}

    public function name(): string
    {
        return 'simulated';
    }

    public function createCharge(int $amountCents, string $method, string $reference): string
    {
        if (!in_array($method, ['pix', 'card'], true)) {
            throw new RuntimeException('Método de pagamento inválido.');
        }
        $externalId = 'SIM-' . bin2hex(random_bytes(12));
        $stmt = $this->db->prepare('INSERT INTO simulated_charges (external_id, status) VALUES (:id, :status)');
        $stmt->execute([':id' => $externalId, ':status' => 'pending']);
        return $externalId;
    }

    public function status(string $externalId): string
    {
        $stmt = $this->db->prepare('SELECT status FROM simulated_charges WHERE external_id = :id');
        $stmt->execute([':id' => $externalId]);
        $row = $stmt->fetch();
        return $row ? (string) $row['status'] : 'unknown';
    }

    /** Ação exclusiva da simulação, para demonstrar o fluxo sem cobrança real. */
    public function simulateApproval(string $externalId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE simulated_charges SET status = 'approved' WHERE external_id = :id AND status = 'pending'"
        );
        $stmt->execute([':id' => $externalId]);
    }
}
