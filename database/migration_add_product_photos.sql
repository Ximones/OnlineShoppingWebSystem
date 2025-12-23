
ALTER TABLE products DROP COLUMN photo;

CREATE TABLE product_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id)
);

INSERT INTO product_photos (product_id, photo_path, is_primary, display_order, created_at)
VALUES
-- Product 1
(1, '/public/uploads/upload_69418315d30219.46343895_OnePieceRound-1.png', 1, 0, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418315d0d115.95397763_OnePieceRound-2.png', 0, 0, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418315cea080.13861663_OnePieceRound-3.png', 0, 0, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418370cfb9a2.80186676_OnePieceRound-4.png', 0, 0, '2025-12-19 11:16:17');