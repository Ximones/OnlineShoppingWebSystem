ALTER TABLE payments
    ADD COLUMN principal_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER amount,
    ADD COLUMN billing_due_date DATE NULL AFTER payment_date,
    ADD COLUMN tenure_months INT NULL AFTER updated_at,
    ADD COLUMN interest_rate DECIMAL(5,2) NULL AFTER tenure_months;



