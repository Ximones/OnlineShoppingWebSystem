
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

(1, '/public/uploads/upload_69418315d30219.46343895_OnePieceRound-1.png', 1, 0, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418315d0d115.95397763_OnePieceRound-2.png', 0, 1, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418315cea080.13861663_OnePieceRound-3.png', 0, 2, '2025-12-19 11:16:17'),
(1, '/public/uploads/upload_69418370cfb9a2.80186676_OnePieceRound-4.png', 0, 3, '2025-12-19 11:16:17'),

-- Product 2
(2, '/public/uploads/upload_69417f277e4f7.69911724_OnePieceElongated-1.png', 1, 0, '2025-12-19 12:04:43'),
(2, '/public/uploads/upload_69417f27a2c2e.87961822_OnePieceElongated-2.png', 0, 1, '2025-12-19 12:04:43'),
(2, '/public/uploads/upload_69417f2761a11.45556566_OnePieceElongated-3.png', 0, 2, '2025-12-19 12:04:43'),
(2, '/public/uploads/upload_69417f360a86e.35500090_OnePieceElongated-4.png', 0, 3, '2025-12-19 12:04:43'),

-- Product 3
(3, '/public/uploads/upload_69418315cea080.13861663_TwoPieceRound-1.jpeg', 1, 0, '2025-12-19 12:30:36'),
(3, '/public/uploads/upload_69418370cfb9a2.80186676_TwoPieceRound-2.jpeg', 0, 1, '2025-12-19 12:30:36'),

-- Product 4
(4, '/public/uploads/upload_694183d2c41c5.886be8.2406232_TwoPieceElongated-1.jpg', 1, 0, '2025-12-19 12:38:59'),
(4, '/public/uploads/upload_694183d039d42.9863332_TwoPieceElongated-2.jpg', 0, 1, '2025-12-19 12:38:59'),
(4, '/public/uploads/upload_6941837db3e94.3774621_TwoPieceElongated-3.jpg', 0, 2, '2025-12-19 12:38:59'),
(4, '/public/uploads/upload_694183db620a1.5404220_TwoPieceElongated-4.jpg', 0, 3, '2025-12-19 12:38:59'),

-- Product 5
(5, '/public/uploads/upload_694183e2d51c5.996be8.2506242_WallMounted-1.png', 1, 0, '2025-12-19 12:48:38'),
(5, '/public/uploads/upload_694183e049d42.9963342_WallMounted-2.png', 0, 1, '2025-12-19 12:48:38'),
(5, '/public/uploads/upload_6941838eb4e94.3874631_WallMounted-3.png', 0, 2, '2025-12-19 12:48:38'),
(5, '/public/uploads/upload_694183ec730a1.5504230_WallMounted-4.png', 0, 3, '2025-12-19 12:48:38'),

-- Product 6
(6, '/public/uploads/upload_694183f1d51c5.996be8.2606252_UltraSlim-1.jpg', 1, 0, '2025-12-19 12:55:16'),
(6, '/public/uploads/upload_694183f049d42.9963352_UltraSlim-2.png', 0, 1, '2025-12-19 12:55:16'),

-- Product 7
(7, '/public/uploads/upload_694184a1d51c5.996be8.2706262_SmartBidet-1.png', 1, 0, '2025-12-19 13:08:08'),
(7, '/public/uploads/upload_694184a049d42.9963362_SmartBidet-2.png', 0, 1, '2025-12-19 13:08:08'),
(7, '/public/uploads/upload_6941848eb4e94.3874631_SmartBidet-3.png', 0, 2, '2025-12-19 13:08:08'),

-- Product 8
(8, '/public/uploads/upload_694184b1d51c5.996be8.2806272_SmartElite-1.png', 1, 0, '2025-12-19 13:01:24'),
(8, '/public/uploads/upload_694184b049d42.9963372_SmartElite-2.png', 0, 1, '2025-12-19 13:01:24');