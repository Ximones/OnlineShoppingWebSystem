-- Migration: Add QR code and pickup location fields to orders table
-- For self-pickup orders, store QR code token and location information

ALTER TABLE orders 
ADD COLUMN qr_code_token VARCHAR(255) NULL UNIQUE AFTER status,
ADD COLUMN pickup_location VARCHAR(255) NULL AFTER shipping_address,
ADD COLUMN item_location VARCHAR(255) NULL AFTER pickup_location,
ADD COLUMN qr_code_generated_at DATETIME NULL AFTER item_location,
ADD INDEX idx_qr_code_token (qr_code_token),
ADD INDEX idx_pickup_location (pickup_location);

-- Update existing pickup orders with default HQ location
UPDATE orders 
SET pickup_location = '12 Jalan Tanjungyew, Kuala Lumpur',
    item_location = 'Warehouse 1 Rack 1'
WHERE shipping_method LIKE '%Pickup%' OR shipping_method LIKE '%pickup%';
