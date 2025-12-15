INSERT INTO users (role, name, email, phone, address, password_hash) VALUES
('admin', 'John Smith', 'john.smith@email.com', '012-3456789', '123 Main Street, Kuala Lumpur, 50000', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W'),
('member', 'Sarah Lee', 'sarah.lee@email.com', '013-4567890', '456 Jalan Ampang, Kuala Lumpur, 50450', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W'),
('member', 'Ahmad Rahman', 'ahmad.rahman@email.com', '014-5678901', '789 Taman Desa, Petaling Jaya, 47400', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W'),
('member', 'Lisa Tan', 'lisa.tan@email.com', '015-6789012', '321 Jalan Bukit Bintang, Kuala Lumpur, 55100', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W'),
('member', 'David Wong', 'david.wong@email.com', '016-7890123', '654 Taman Tun Dr Ismail, Kuala Lumpur, 60000', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W');

INSERT INTO categories (name, description) VALUES
('One-Piece Toilet', 'Modern one-piece toilet bowls with integrated tank'),
('Two-Piece Toilet', 'Traditional two-piece toilet bowls with separate tank'),
('Wall-Mounted Toilet', 'Space-saving wall-mounted toilet bowls'),
('Smart Toilet', 'Advanced smart toilet bowls with bidet and heating features'),
('Compact Toilet', 'Space-efficient compact toilet bowls for small bathrooms'),
('Accessories', 'Brushes, cleaners and other toilet accessories');

INSERT INTO products (category_id, sku, name, description, price, stock, photo) VALUES
(1, 'TLT-1001', 'Elite One-Piece Round Toilet', 'Premium one-piece round toilet with soft-close seat. Water-saving dual flush system. Easy to clean design.', 899.00, 25, '/images/toilet1.jpg'),
(1, 'TLT-1002', 'Deluxe One-Piece Elongated Toilet', 'Luxury one-piece elongated toilet with comfort height. Dual flush technology. Ceramic glazed finish.', 1299.00, 15, '/images/toilet2.jpg'),
(2, 'TLT-2001', 'Classic Two-Piece Round Toilet', 'Traditional two-piece round toilet. Reliable gravity flush system. Standard height.', 499.00, 40, '/images/toilet3.jpg'),
(2, 'TLT-2002', 'Premium Two-Piece Elongated Toilet', 'High-quality two-piece elongated toilet. Power flush system. Comfort height design.', 799.00, 30, '/images/toilet4.jpg'),
(3, 'TLT-3001', 'Modern Wall-Mounted Toilet', 'Contemporary wall-mounted toilet with concealed cistern. Dual flush system.', 1599.00, 12, '/images/toilet5.jpg'),
(3, 'TLT-3002', 'Ultra-Slim Wall-Mounted Toilet', 'Sleek wall-mounted toilet perfect for modern bathrooms. Easy maintenance. Water-efficient.', 1399.00, 18, '/images/toilet6.jpg'),
(4, 'TLT-4001', 'Smart Bidet Toilet Pro', 'Advanced smart toilet with heated seat, warm water bidet, air dryer, and night light. Touchless operation.', 2999.00, 8, '/images/toilet7.jpg'),
(4, 'TLT-4002', 'Smart Toilet Elite Plus', 'Premium smart toilet with self-cleaning, deodorizer, and remote control.', 3999.00, 5, '/images/toilet8.jpg'),
(5, 'TLT-5001', 'Compact Round Toilet', 'Space-saving compact round toilet ideal for small bathrooms. Standard flush system.', 399.00, 35, '/images/toilet9.jpg'),
(5, 'TLT-5002', 'Compact Elongated Toilet', 'Compact elongated toilet with comfort features. Perfect for tight spaces.', 549.00, 28, '/images/toilet10.jpg');

INSERT INTO carts (user_id, status) VALUES
(1, 'open'),
(2, 'open'),
(3, 'open'),
(4, 'open');

INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(1, 3, 2),
(1, 7, 1),
(2, 1, 1),
(3, 5, 1),
(4, 2, 1),
(4, 9, 2);

INSERT INTO orders (user_id, total_amount, status, shipping_name, shipping_phone, shipping_address) VALUES
(1, 3797.00, 'completed', 'John Smith', '012-3456789', '123 Main Street, Kuala Lumpur, 50000'),
(2, 899.00, 'shipped', 'Sarah Lee', '013-4567890', '456 Jalan Ampang, Kuala Lumpur, 50450'),
(3, 1599.00, 'processing', 'Ahmad Rahman', '014-5678901', '789 Taman Desa, Petaling Jaya, 47400'),
(5, 3898.00, 'completed', 'David Wong', '016-7890123', '654 Taman Tun Dr Ismail, Kuala Lumpur, 60000');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 7, 1, 2999.00),
(1, 9, 2, 399.00),
(2, 1, 1, 899.00),
(3, 5, 1, 1599.00),
(4, 7, 1, 2999.00),
(4, 1, 1, 899.00);

INSERT INTO payments (order_id, payment_method, amount, status, transaction_ref) VALUES
(1, 'Credit Card', 3797.00, 'completed', 'TXN-2024-001234'),
(2, 'Online Banking', 899.00, 'completed', 'TXN-2024-001235'),
(3, 'Credit Card', 1599.00, 'pending', 'TXN-2024-001236'),
(4, 'E-Wallet', 3898.00, 'completed', 'TXN-2024-001237');

INSERT INTO tracking_details (order_id, status, location, remarks) VALUES
(1, 'Delivered', 'Kuala Lumpur Distribution Center', 'Package delivered successfully to customer'),
(1, 'Out for Delivery', 'Kuala Lumpur', 'Package is out for delivery'),
(1, 'In Transit', 'Shah Alam Hub', 'Package in transit to delivery location'),
(2, 'In Transit', 'Petaling Jaya Hub', 'Package is being transported to delivery center'),
(2, 'Shipped', 'Kuala Lumpur Warehouse', 'Package has been shipped'),
(3, 'Processing', 'Kuala Lumpur Warehouse', 'Order is being prepared for shipment'),
(4, 'Delivered', 'Kuala Lumpur Distribution Center', 'Package delivered successfully'),
(4, 'Out for Delivery', 'Kuala Lumpur', 'Package is out for delivery');
