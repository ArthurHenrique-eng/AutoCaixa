<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Http;
use App\Core\Permissions;
use App\Core\View;
use PDO;

/** Login, painel inicial, usuários e auditoria. */
final class AdminController
{
    public function __construct(private PDO $db) {}

    public function loginForm(): void
    {
        if (Auth::user($this->db) !== null) {
            Http::redirect('/admin');
            return;
        }
        View::render('admin/login', ['error' => null]);
    }

    public function login(): void
    {
        if (!Http::formCsrfOk()) {
            View::render('admin/login', ['error' => 'Sessão expirada. Tente novamente.'], 419);
            return;
        }
        $email = Http::input('email', 160);
        $password = (string) ($_POST['password'] ?? '');
        $result = Auth::attempt($this->db, $email, $password, Auth::ip());

        if ($result === 'ok') {
            Http::redirect('/admin');
            return;
        }
        $message = $result === 'locked'
            ? 'Acesso bloqueado temporariamente por excesso de tentativas. Aguarde alguns minutos.'
            : 'E-mail ou senha inválidos.';
        View::render('admin/login', ['error' => $message], $result === 'locked' ? 429 : 401);
    }

    public function logout(): void
    {
        if (Http::formCsrfOk()) {
            Auth::logout($this->db);
        }
        Http::redirect('/admin/login');
    }

    public function dashboard(): void
    {
        $user = $this->requireUser('dashboard.view');
        if ($user === null) return;

        $today = $this->db->query(
            "SELECT COUNT(*) AS n, COALESCE(SUM(total_cents),0) AS total
             FROM sales WHERE status = 'paid' AND DATE(updated_at) = CURDATE()"
        )->fetch();
        $awaiting = (int) $this->db->query("SELECT COUNT(*) FROM sales WHERE status = 'awaiting_payment'")->fetchColumn();
        $failedToday = (int) $this->db->query(
            "SELECT COUNT(*) FROM sales WHERE status = 'failed' AND DATE(updated_at) = CURDATE()"
        )->fetchColumn();
        $lowStock = $this->db->query(
            'SELECT name, barcode, stock, min_stock FROM products
             WHERE active = 1 AND stock <= min_stock ORDER BY stock ASC LIMIT 20'
        )->fetchAll();

        View::render('admin/dashboard', [
            'user' => $user, 'todayCount' => (int) $today['n'], 'todayTotal' => (int) $today['total'],
            'awaiting' => $awaiting, 'failedToday' => $failedToday, 'lowStock' => $lowStock,
        ]);
    }

    public function users(): void
    {
        $user = $this->requireUser('users.manage');
        if ($user === null) return;
        $rows = $this->db->query(
            'SELECT id, name, email, role, active, last_login_at FROM users ORDER BY name'
        )->fetchAll();
        View::render('admin/users', ['user' => $user, 'rows' => $rows, 'roles' => Permissions::roles()]);
    }

    public function userCreate(): void
    {
        $user = $this->requireUser('users.manage');
        if ($user === null || !$this->formOk('/admin/usuarios')) return;

        $name = Http::input('name', 120);
        $email = strtolower(Http::input('email', 160));
        $role = Http::input('role', 20);
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            { $this->back('/admin/usuarios', 'error', 'Informe nome e um e-mail válido.'); return; }
        }
        if (!in_array($role, Permissions::roles(), true)) {
            { $this->back('/admin/usuarios', 'error', 'Perfil inválido.'); return; }
        }
        if (strlen($password) < 12) {
            { $this->back('/admin/usuarios', 'error', 'A senha inicial deve ter pelo menos 12 caracteres.'); return; }
        }
        try {
            $this->db->prepare(
                'INSERT INTO users (name, email, password_hash, role) VALUES (:n, :e, :h, :r)'
            )->execute([':n' => $name, ':e' => $email, ':h' => Auth::hashPassword($password), ':r' => $role]);
        } catch (\PDOException $e) {
            { $this->back('/admin/usuarios', 'error', 'Já existe um usuário com este e-mail.'); return; }
        }
        Audit::log($this->db, (int) $user['id'], 'user_created', 'user', (string) $this->db->lastInsertId(),
            ['role' => $role], Auth::ip());
        $this->back('/admin/usuarios', 'ok', 'Usuário criado. Peça para trocar a senha no primeiro acesso.');
    }

    public function userToggle(int $id): void
    {
        $user = $this->requireUser('users.manage');
        if ($user === null || !$this->formOk('/admin/usuarios')) return;
        if ($id === (int) $user['id']) {
            { $this->back('/admin/usuarios', 'error', 'Você não pode desativar a própria conta.'); return; }
        }
        $this->db->prepare('UPDATE users SET active = IF(active = 1, 0, 1) WHERE id = :id')->execute([':id' => $id]);
        Audit::log($this->db, (int) $user['id'], 'user_toggled', 'user', (string) $id, null, Auth::ip());
        $this->back('/admin/usuarios', 'ok', 'Situação do usuário atualizada.');
    }

    public function audit(): void
    {
        $user = $this->requireUser('audit.view');
        if ($user === null) return;
        $rows = $this->db->query(
            'SELECT a.created_at, a.action, a.entity, a.entity_id, a.details, a.ip, u.email
             FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC LIMIT 300'
        )->fetchAll();
        View::render('admin/audit', ['user' => $user, 'rows' => $rows]);
    }

    /** Exige login e permissão. Retorna o usuário ou null após responder. */
    public function requireUser(string $permission): ?array
    {
        $user = Auth::user($this->db);
        if ($user === null) {
            Http::redirect('/admin/login');
            return null;
        }
        if (!Permissions::allows($user['role'], $permission)) {
            Http::forbidden();
            return null;
        }
        return $user;
    }

    private function formOk(string $backUrl): bool
    {
        if (!Http::formCsrfOk()) {
            $this->back($backUrl, 'error', 'Sessão expirada. Tente novamente.');
            return false;
        }
        return true;
    }

    private function back(string $url, string $type, string $message): void
    {
        Http::flash($type, $message);
        Http::redirect($url);
    }
}
