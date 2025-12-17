ALTER TABLE orders
    ADD COLUMN shipping_method VARCHAR(50) NULL AFTER shipping_address,
    ADD COLUMN points_discount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER shipping_method,
    ADD COLUMN voucher_discount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER points_discount;

