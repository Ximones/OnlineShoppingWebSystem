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
(1, '/public/uploads/upload_694183baa87d30.62578953_OnePieceRound-1.png', 1, 0, NOW()),
(1, '/public/uploads/upload_694183c4cf8aa6.00621264_OnePieceRound-2.png', 0, 0, NOW()),
(1, '/public/uploads/upload_694183cdb620a1.54042201_OnePieceRound-3.png', 0, 0, NOW()),
(1, '/public/uploads/upload_694183db2c41c5.40138647_OnePieceRound-4.png', 0, 0, NOW());