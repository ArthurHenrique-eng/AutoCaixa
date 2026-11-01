-- AutoCaixa Local — migração 004 (endurecimento de segurança)
-- Senha provisória (obriga troca no primeiro acesso) e contador para o limitador de requisições.

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash;

-- Contador de requisições por "bucket" (rota + ator) e janela fixa. Ver App\Core\RateLimiter.
CREATE TABLE IF NOT EXISTS rate_limit_hits (
    bucket       VARCHAR(160)    NOT NULL,
    window_start INT UNSIGNED    NOT NULL,
    hits         INT UNSIGNED    NOT NULL DEFAULT 1,
    PRIMARY KEY (bucket, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
