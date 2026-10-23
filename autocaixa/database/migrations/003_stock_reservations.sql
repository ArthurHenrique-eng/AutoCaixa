-- AutoCaixa Local — migração 003
-- Reserva temporária de estoque para impedir overselling entre checkouts concorrentes.
-- A reserva pertence à venda e é convertida em baixa definitiva somente quando o pagamento é confirmado.

ALTER TABLE products
    ADD COLUMN reserved_stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER stock;

ALTER TABLE products
    ADD CONSTRAINT chk_products_reserved_stock CHECK (reserved_stock <= stock);

CREATE INDEX idx_products_reserved_stock ON products (reserved_stock);
