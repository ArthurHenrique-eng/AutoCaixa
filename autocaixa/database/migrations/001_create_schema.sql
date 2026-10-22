-- AutoCaixa Local — migração 001 (Fase 1)
-- Engine InnoDB, charset utf8mb4 e collation adequada ao português.
-- Valores monetários são armazenados em centavos (INT), sem ponto flutuante.

CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    barcode      VARCHAR(64)  NOT NULL,
    name         VARCHAR(160) NOT NULL,
    price_cents  INT UNSIGNED NOT NULL,
    stock        INT UNSIGNED NOT NULL DEFAULT 0,
    min_stock    INT UNSIGNED NOT NULL DEFAULT 0,
    active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_products_barcode (barcode),
    KEY idx_products_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status       ENUM('awaiting_payment','paid','failed','cancelled','expired') NOT NULL,
    total_cents  INT UNSIGNED NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_sales_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sale_items (
    id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id            BIGINT UNSIGNED NOT NULL,
    product_id         INT UNSIGNED    NOT NULL,
    product_name       VARCHAR(160)    NOT NULL,
    unit_price_cents   INT UNSIGNED    NOT NULL,
    quantity           INT UNSIGNED    NOT NULL,
    subtotal_cents     INT UNSIGNED    NOT NULL,
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales (id),
    CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products (id),
    KEY idx_sale_items_sale (sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id        BIGINT UNSIGNED NOT NULL,
    method         ENUM('pix','card') NOT NULL,
    status         ENUM('pending','approved','declined','cancelled','expired','failed','unknown') NOT NULL,
    amount_cents   INT UNSIGNED NOT NULL,
    provider       VARCHAR(32)  NOT NULL,
    external_id    VARCHAR(100) NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payments_external (provider, external_id),
    CONSTRAINT fk_payments_sale FOREIGN KEY (sale_id) REFERENCES sales (id),
    KEY idx_payments_sale (sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movements (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED NOT NULL,
    quantity_change INT NOT NULL,
    reason          VARCHAR(40) NOT NULL,
    sale_id         BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES products (id),
    CONSTRAINT fk_stock_movements_sale FOREIGN KEY (sale_id) REFERENCES sales (id),
    KEY idx_stock_movements_sale (sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estado de cobranças simuladas. Existe somente no driver de simulação, nunca em produção.
CREATE TABLE IF NOT EXISTS simulated_charges (
    external_id  VARCHAR(100) NOT NULL PRIMARY KEY,
    status       ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
