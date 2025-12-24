ALTER TABLE users
ADD login_attempts int(11) NOT NULL DEFAULT 0 AFTER updated_at,
ADD lockout_until datetime DEFAULT NULL AFTER login_attempts;