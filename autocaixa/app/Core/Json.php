<?php
declare(strict_types=1);

namespace App\Core;

final class Json
{
    public static function send(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Formato padrão de erro da API: {"error": {"code": "...", "message": "..."}}.
     * "message" é sempre um texto seguro para o cliente; detalhes técnicos vão só para o log.
     */
    public static function error(string $code, string $message, int $status): void
    {
        self::send(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
