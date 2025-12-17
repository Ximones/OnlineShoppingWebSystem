ALTER TABLE users
    ADD COLUMN paylater_credit_limit DECIMAL(10,2) NOT NULL DEFAULT 10000.00 AFTER last_check_in_at;

-- Set default credit limit for existing users
UPDATE users SET paylater_credit_limit = 10000.00 WHERE paylater_credit_limit = 0 OR paylater_credit_limit IS NULL;

