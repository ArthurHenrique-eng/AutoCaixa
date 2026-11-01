<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Autenticação do painel. Senhas com password_hash, bloqueio após falhas repetidas,
 * regeneração do identificador de sessão no login e verificação de perfil a cada ação.
 * A sessão administrativa também expira por inatividade e por tempo absoluto.
 */
final class Auth
{
    public const MAX_FAILED = 5;
    public const LOCK_MINUTES = 15;

    /** Expiração da sessão do admin. Não afeta a sessão do totem (chaves de sessão diferentes). */
    public const IDLE_SECONDS = 900;       // 15 minutos sem atividade no painel
    public const ABSOLUTE_SECONDS = 28800; // 8 horas desde o login, mesmo com atividade

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
        $now = time();
        $_SESSION['admin_login_at'] = $now;
        $_SESSION['admin_last_activity'] = $now;
        Audit::log($db, (int) $user['id'], 'login', 'auth', (string) $user['id'], null, $ip);
        return 'ok';
    }

    public static function logout(PDO $db): void
    {
        $uid = self::userId();
        if ($uid !== null) {
            Audit::log($db, $uid, 'logout', 'auth', (string) $uid, null, self::ip());
        }
        self::destroySession();
    }

    /**
     * Usuário autenticado da sessão administrativa, ou null se não houver login válido.
     * Também encerra (e audita) a sessão quando ela expirou por inatividade ou por tempo absoluto.
     */
    public static function user(PDO $db): ?array
    {
        $uid = self::userId();
        if ($uid === null) {
            return null;
        }

        $now = time();
        $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? 0);
        $loginAt = (int) ($_SESSION['admin_login_at'] ?? 0);
        $idleExpired = $lastActivity > 0 && ($now - $lastActivity) > self::IDLE_SECONDS;
        $absoluteExpired = $loginAt > 0 && ($now - $loginAt) > self::ABSOLUTE_SECONDS;
        if ($idleExpired || $absoluteExpired) {
            Audit::log($db, $uid, 'session_expired', 'auth', (string) $uid, null, self::ip());
            self::destroySession();
            return null;
        }

        $stmt = $db->prepare(
            'SELECT id, name, email, role, active, must_change_password FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $uid]);
        $user = $stmt->fetch();
        if ($user === false || (int) $user['active'] !== 1) {
            self::destroySession();
            return null;
        }
        $_SESSION['admin_last_activity'] = $now;
        return $user;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function ip(): string
    {
        $config = $GLOBALS['appConfig'] ?? null;
        $trustedProxies = is_array($config) ? ($config['trusted_proxies'] ?? []) : [];
        return Ip::client($trustedProxies);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Encerra a sessão por completo: limpa os dados, remove o cookie do navegador e começa
     * uma sessão nova (vazia), para que CSRF e outras páginas públicas continuem funcionando.
     */
    private static function destroySession(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
        }
        session_start();
        session_regenerate_id(true);
    }
}
