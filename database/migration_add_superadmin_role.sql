-- Migration: Add superadmin role
-- This migration adds the 'superadmin' role to the system

-- Insert superadmin role into roles table (if roles table exists)
INSERT INTO roles (code, name) VALUES ('superadmin', 'Super Admin')
ON DUPLICATE KEY UPDATE name = 'Super Admin';

-- Note: If you need to convert an existing admin to superadmin, run:
-- UPDATE users SET role = 'superadmin' WHERE email = 'your-superadmin-email@example.com';

