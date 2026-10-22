-- Produtos de desenvolvimento (dados fictícios). Não usar em produção.
INSERT INTO products (barcode, name, price_cents, stock, min_stock, active) VALUES
('7890000000011', 'Café 500 g', 1890, 50, 5, 1),
('7890000000028', 'Leite integral 1 L', 549, 80, 10, 1),
('7890000000035', 'Pão de forma 500 g', 790, 30, 5, 1),
('7890000000042', 'Açúcar 1 kg', 499, 40, 5, 1),
('7890000000059', 'Produto inativo (teste)', 999, 10, 0, 0)
ON DUPLICATE KEY UPDATE name = VALUES(name);
