<?php
declare(strict_types=1);

namespace App\Core;

/** Utilidades de requisição, redirecionamento, mensagens e validação de formulários do painel. */
final class Http
{
    public static function redirect(string $url): void
    {
        header('Location: ' . $url, true, 303);
    }

    public static function forbidden(): void
    {
        View::render('error', ['message' => 'Você não tem permissão para esta ação.'], 403);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Valida o token CSRF enviado no campo _csrf de um formulário. */
    public static function formCsrfOk(): bool
    {
        return Csrf::validate($_POST['_csrf'] ?? null);
    }

    public static function input(string $key, int $max = 255): string
    {
        return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
    }

    public static function validDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    }
}
