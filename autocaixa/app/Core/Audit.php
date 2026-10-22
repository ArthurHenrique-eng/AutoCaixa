<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Registro de auditoria. Nunca grava senhas, tokens ou dados financeiros sensíveis. */
final class Audit
{
    private const FORBIDDEN_KEYS = ['password', 'senha', 'token', 'secret', 'card', 'cartao', 'cvv'];

    public static function log(PDO $db, ?int $userId, string $action, string $entity, ?string $entityId, ?array $details, string $ip): void
    {
        $safe = null;
        if ($details !== null) {
            $safe = [];
            foreach ($details as $k => $v) {
                foreach (self::FORBIDDEN_KEYS as $bad) {
                    if (stripos((string) $k, $bad) !== false) {
                        continue 2;
                    }
                }
                $safe[$k] = $v;
            }
            $safe = json_encode($safe, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        $db->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip)
             VALUES (:uid, :action, :entity, :eid, :details, :ip)'
        )->execute([
            ':uid' => $userId, ':action' => $action, ':entity' => $entity,
            ':eid' => $entityId, ':details' => $safe, ':ip' => $ip,
        ]);
    }
}
