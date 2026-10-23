-- AutoCaixa Local — migração 003
-- Reserva temporária de estoque para impedir overselling entre checkouts concorrentes.
-- A reserva pertence à venda e é convertida em baixa definitiva somente quando o pagamento é confirmado.

SET @column_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'products'
      AND column_name = 'reserved_stock'
);
SET @sql := IF(
    @column_exists = 0,
    'ALTER TABLE products ADD COLUMN reserved_stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER stock',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'products'
      AND constraint_name = 'chk_products_reserved_stock'
);
SET @sql := IF(
    @constraint_exists = 0,
    'ALTER TABLE products ADD CONSTRAINT chk_products_reserved_stock CHECK (reserved_stock <= stock)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'products'
      AND index_name = 'idx_products_reserved_stock'
);
SET @sql := IF(
    @index_exists = 0,
    'CREATE INDEX idx_products_reserved_stock ON products (reserved_stock)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
