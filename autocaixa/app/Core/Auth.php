<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Autenticação do painel. Senhas com password_hash, bloqueio após falhas repetidas,
 * regeneração do identificador de sessão no login e verificação de perfil a cada ação.
 */
final class Auth
{
    public const MAX_FAILED = 5;
    public const LOCK_MINUTES = 15;

    /** Hash fixo usado para equalizar o tempo quando o e-mail não existe. */
    private const DUMMY_HASH = '$2y$12$CwTycUXWue0Thq9StjUM0uJ8.G8OBrJJb3d4C2nVVYt3tkrc3e0vO';

    public static function attempt(PDO $db, string $email, string $password, string $ip): string
    {
        $email = strtolower(trim($email));
        $stmt = $db->prepare(
            'SELECT id, password_hash, role, active, failed_attempts,
                    (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
             FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user === false) {
            password_verify($password, self::DUMMY_HASH);
            Audit::log($db, null, 'login_failed', 'auth', null, ['email' => $email], $ip);
            return 'invalid';
        }
        if ((int) $user['is_locked'] === 1) {
            return 'locked';
        }

        $ok = password_verify($password, $user['password_hash']) && (int) $user['active'] === 1;
        if (!$ok) {
            $failed = (int) $user['failed_attempts'] + 1;
            $lock = $failed >= self::MAX_FAILED;
            $db->prepare(
                'UPDATE users SET failed_attempts = :f,
                 locked_until = IF(:lock = 1, DATE_ADD(NOW(), INTERVAL :mins MINUTE), NULL)
                 WHERE id = :id'
            )->execute([
                ':f' => $lock ? 0 : $failed, ':lock' => $lock ? 1 : 0,
                ':mins' => self::LOCK_MINUTES, ':id' => $user['id'],
            ]);
            Audit::log($db, (int) $user['id'], 'login_failed', 'auth', (string) $user['id'], null, $ip);
            return $lock ? 'locked' : 'invalid';
        }

        $db->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => $user['id']]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        Audit::log($db, (int) $user['id'], 'login', 'auth', (string) $user['id'], null, $ip);
        return 'ok';
    }

    public static function logout(PDO $db): void
    {
        $uid = self::userId();
        if ($uid !== null) {
            Audit::log($db, $uid, 'logout', 'auth', (string) $uid, null, self::ip());
        }
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function user(PDO $db): ?array
    {
        $uid = self::userId();
        if ($uid === null) {
            return null;
        }
        $stmt = $db->prepare('SELECT id, name, email, role, active FROM users WHERE id = :id');
        $stmt->execute([':id' => $uid]);
        $user = $stmt->fetch();
        if ($user === false || (int) $user['active'] !== 1) {
            unset($_SESSION['user_id']);
            return null;
        }
        return $user;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
