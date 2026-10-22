-- AutoCaixa Local — migração 002 (Fase 2: painel administrativo)

CREATE TABLE IF NOT EXISTS users (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(120) NOT NULL,
    email            VARCHAR(160) NOT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    role             ENUM('admin','manager','cashier') NOT NULL,
    active           TINYINT(1)   NOT NULL DEFAULT 1,
    failed_attempts  INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until     DATETIME     NULL,
    last_login_at    DATETIME     NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    action       VARCHAR(60)  NOT NULL,
    entity       VARCHAR(40)  NOT NULL,
    entity_id    VARCHAR(40)  NULL,
    details      TEXT         NULL,
    ip           VARCHAR(45)  NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id),
    KEY idx_audit_created (created_at),
    KEY idx_audit_entity (entity, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE stock_movements ADD COLUMN user_id INT UNSIGNED NULL,
    ADD CONSTRAINT fk_stock_movements_user FOREIGN KEY (user_id) REFERENCES users (id);
