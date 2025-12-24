-- Add image_url column to categories table
ALTER TABLE categories
ADD COLUMN image_url VARCHAR(255) NULL AFTER description;

