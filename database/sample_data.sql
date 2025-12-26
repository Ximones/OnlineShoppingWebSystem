-- ============================================
-- Online Shopping Web System - Sample Data
-- Final consolidated version with all sample data
-- ============================================

-- ============================================
-- Users
-- Password for all test users: password123
-- ============================================
INSERT INTO users (role, name, email, phone, address, password_hash, email_verified_at, reward_points, reward_tier) VALUES
('admin', 'John Smith', 'admin@example.com', '012-3456789', '123 Main Street, Kuala Lumpur, 50000', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W', NOW(), 5000, 'gold'),
('member', 'Sarah Lee', 'sarah.lee@example.com', '013-4567890', '456 Jalan Ampang, Kuala Lumpur, 50450', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W', NOW(), 2500, 'silver'),
('member', 'Ahmad Rahman', 'ahmad.rahman@example.com', '014-5678901', '789 Taman Desa, Petaling Jaya, 47400', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W', NOW(), 1000, 'bronze'),
('member', 'Lisa Tan', 'lisa.tan@example.com', '015-6789012', '321 Jalan Bukit Bintang, Kuala Lumpur, 55100', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W', NOW(), 500, 'bronze'),
('member', 'David Wong', 'david.wong@example.com', '016-7890123', '654 Taman Tun Dr Ismail, Kuala Lumpur, 60000', '$2y$10$ZpBoeJ1i8LojRxurx8Wc.e1zVqhlVg3I8I4JVXuZWfLG3gNsYch0W', NOW(), 3000, 'silver');

-- ============================================
-- Categories
-- ============================================
INSERT INTO categories (name, description) VALUES
('One-Piece Toilet', 'Modern one-piece toilet bowls with integrated tank'),
('Two-Piece Toilet', 'Traditional two-piece toilet bowls with separate tank'),
('Wall-Mounted Toilet', 'Space-saving wall-mounted toilet bowls'),
('Smart Toilet', 'Advanced smart toilet bowls with bidet and heating features'),
('Compact Toilet', 'Space-efficient compact toilet bowls for small bathrooms'),
('Brushes', 'Brushes and related toilet cleaning accessories'),
('Bidets', 'Standalone and add-on bidet products'),
('Seats', 'Toilet seats and covers'),
('Mats', 'Bathroom and toilet floor mats');

-- ============================================
-- Products
-- ============================================
INSERT INTO products (category_id, sku, name, description, color, size, pit_spacing, installation_type, flushing_method, bowl_shape, material, warranty_years, price, stock, status) VALUES
(1, 'TLT-1001', 'Elite One-Piece Round Toilet', 'The Elite One-Piece Round Toilet combines contemporary design with superior functionality. Featuring an integrated soft-close seat mechanism, this premium fixture offers exceptional comfort and durability. The seamless one-piece construction ensures easy maintenance while the efficient dual-flush system promotes water conservation without compromising performance.', 'Pure White', '660mm × 380mm × 780mm', '305mm', 'Floor-Mounted', 'Dual Flush (3L/6L)', 'Round', 'High-Grade Vitreous China', 2, 899.00, 25, 'active'),
(1, 'TLT-1002', 'Deluxe One-Piece Elongated Toilet', 'Engineered for ultimate comfort, the Deluxe One-Piece Elongated Toilet features an extended bowl design that provides enhanced seating comfort. The powerful siphon jet flushing system ensures thorough bowl cleaning with every flush. Constructed from premium vitreous china with a durable glaze finish that resists staining and maintains its pristine appearance.', 'Ivory White', '700mm × 380mm × 800mm', '305mm', 'Floor-Mounted', 'Siphon Jet (4.8L)', 'Elongated', 'Premium Vitreous China', 2, 1299.00, 15, 'active'),
(2, 'TLT-2001', 'Classic Two-Piece Round Toilet', 'The Classic Two-Piece Round Toilet represents timeless design and reliable performance. Its traditional gravity flush system delivers consistent, powerful flushing action. The two-piece construction allows for easier transportation and installation, making it an ideal choice for both residential and commercial applications.', 'Brilliant White', '650mm × 370mm × 760mm', '305mm', 'Floor-Mounted', 'Gravity Flush (6L)', 'Round', 'Vitreous China', 2, 499.00, 40, 'active'),
(2, 'TLT-2002', 'Premium Two-Piece Elongated Toilet', 'Experience superior performance with the Premium Two-Piece Elongated Toilet. Equipped with an advanced power flush system, this fixture ensures complete waste removal while maintaining water efficiency. The elongated bowl provides exceptional comfort, while the high-grade vitreous china construction guarantees long-lasting durability and easy maintenance.', 'Glossy White', '710mm × 380mm × 785mm', '305mm', 'Floor-Mounted', 'Power Flush (4.8L)', 'Elongated', 'High-Grade Vitreous China', 2, 799.00, 30, 'active'),
(3, 'TLT-3001', 'Modern Wall-Mounted Toilet', 'The Modern Wall-Mounted Toilet epitomizes contemporary bathroom design. Its floating installation creates a spacious, minimalist aesthetic while facilitating effortless floor cleaning. The concealed cistern system is integrated within the wall cavity, providing a seamless appearance. The rimless bowl design ensures superior hygiene and simplified maintenance.', 'Matte White', '530mm × 360mm × 340mm', '180mm / 230mm (Adjustable)', 'Wall-Mounted', 'Concealed Cistern Dual Flush (3L/6L)', 'Round', 'Premium Ceramic', 2, 1599.00, 12, 'active'),
(3, 'TLT-3002', 'Ultra-Slim Wall-Mounted Toilet', 'Designed for modern living spaces, the Ultra-Slim Wall-Mounted Toilet combines sleek aesthetics with advanced functionality. The ultra-thin profile maximizes bathroom space while the nano-glaze ceramic surface provides exceptional stain resistance and antibacterial properties. The water-efficient dual flush system significantly reduces water consumption.', 'Alpine White', '520mm × 350mm × 330mm', '180mm / 230mm (Adjustable)', 'Wall-Mounted', 'Concealed Cistern Dual Flush (3L/4.5L)', 'Elongated', 'Nano-Glaze Ceramic', 2, 1399.00, 18, 'active'),
(4, 'TLT-4001', 'Smart Bidet Toilet Pro', 'The Smart Bidet Toilet Pro represents the pinnacle of bathroom technology. Features include a heated seat with adjustable temperature settings, warm water bidet with customizable pressure and position, automatic deodorization system, and soft-closing lid. The integrated night light and energy-saving mode enhance user convenience while the self-cleaning nozzle ensures optimal hygiene.', 'Ceramic White', '700mm × 400mm × 520mm', '305mm', 'Floor-Mounted', 'Automatic Dual Flush (3L/4.5L)', 'Elongated', 'Antibacterial Ceramic', 2, 2999.00, 8, 'active'),
(4, 'TLT-4002', 'Smart Toilet Elite Plus', 'The Smart Toilet Elite Plus delivers an unparalleled luxury experience with comprehensive automation. Advanced features include hands-free automatic lid opening and closing, personalized user profiles with memory settings, UV sterilization system, built-in air dryer with adjustable temperature, and wireless remote control. The tankless instant heating system provides unlimited warm water supply.', 'Pearl White', '720mm × 410mm × 530mm', '305mm', 'Floor-Mounted', 'Intelligent Auto Flush (3L/4.5L)', 'Elongated', 'Premium Antibacterial Ceramic', 2, 3999.00, 5, 'active'),
(5, 'TLT-5001', 'Compact Round Toilet', 'Specifically engineered for compact spaces, the Compact Round Toilet delivers full functionality without compromising on quality. The space-efficient design makes it perfect for powder rooms, small bathrooms, or ensuites. Despite its reduced footprint, it maintains excellent flushing performance and comfort, featuring a standard-height seat and efficient water-saving dual flush mechanism.', 'Bright White', '610mm × 340mm × 740mm', '305mm', 'Floor-Mounted', 'Dual Flush (3L/4.5L)', 'Round', 'Space-Grade Ceramic', 2, 399.00, 35, 'active'),
(5, 'TLT-5002', 'Compact Elongated Toilet', 'The Compact Elongated Toilet offers the perfect balance between space efficiency and comfort. While maintaining a reduced overall footprint, the elongated bowl provides enhanced seating comfort typically found in full-sized models. The power-assisted flushing system ensures reliable performance, and the comfort-height design meets ADA accessibility standards, making it suitable for users of all ages.', 'Soft White', '640mm × 360mm × 755mm', '305mm', 'Floor-Mounted', 'Power-Assisted Dual Flush (3L/4.8L)', 'Elongated', 'High-Density Ceramic', 2, 549.00, 28, 'active');

-- ============================================
-- Carts
-- ============================================
INSERT INTO carts (user_id, status) VALUES
(2, 'open'),
(3, 'open'),
(4, 'open'),
(5, 'open');

-- ============================================
-- Cart Items
-- ============================================
INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(1, 3, 2),
(1, 7, 1),
(2, 1, 1),
(3, 5, 1),
(4, 2, 1),
(4, 9, 2);

-- ============================================
-- Orders
-- ============================================
INSERT INTO orders (user_id, cart_id, total_amount, status, shipping_name, shipping_phone, shipping_address, shipping_method, points_discount, voucher_discount) VALUES
(2, 1, 3797.00, 'completed', 'Sarah Lee', '013-4567890', '456 Jalan Ampang, Kuala Lumpur, 50450', 'standard', 0.00, 0.00),
(3, 2, 899.00, 'shipped', 'Ahmad Rahman', '014-5678901', '789 Taman Desa, Petaling Jaya, 47400', 'express', 0.00, 0.00),
(4, 3, 1599.00, 'processing', 'Lisa Tan', '015-6789012', '321 Jalan Bukit Bintang, Kuala Lumpur, 55100', 'standard', 0.00, 0.00),
(5, 4, 3898.00, 'completed', 'David Wong', '016-7890123', '654 Taman Tun Dr Ismail, Kuala Lumpur, 60000', 'standard', 0.00, 0.00);

-- ============================================
-- Order Items
-- ============================================
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 7, 1, 2999.00),
(1, 9, 2, 399.00),
(2, 1, 1, 899.00),
(3, 5, 1, 1599.00),
(4, 7, 1, 2999.00),
(4, 1, 1, 899.00);

-- ============================================
-- Payments
-- ============================================
INSERT INTO payments (order_id, payment_method, amount, principal_amount, status, transaction_ref) VALUES
(1, 'Stripe', 3797.00, 3797.00, 'completed', 'TXN-2024-001234'),
(2, 'Stripe', 899.00, 899.00, 'completed', 'TXN-2024-001235'),
(3, 'Stripe', 1599.00, 1599.00, 'pending', 'TXN-2024-001236'),
(4, 'Stripe', 3898.00, 3898.00, 'completed', 'TXN-2024-001237');

-- ============================================
-- Tracking Details
-- ============================================
INSERT INTO tracking_details (order_id, status, location, remarks) VALUES
(1, 'Delivered', 'Kuala Lumpur Distribution Center', 'Package delivered successfully to customer'),
(1, 'Out for Delivery', 'Kuala Lumpur', 'Package is out for delivery'),
(1, 'In Transit', 'Shah Alam Hub', 'Package in transit to delivery location'),
(2, 'In Transit', 'Petaling Jaya Hub', 'Package is being transported to delivery center'),
(2, 'Shipped', 'Kuala Lumpur Warehouse', 'Package has been shipped'),
(3, 'Processing', 'Kuala Lumpur Warehouse', 'Order is being prepared for shipment'),
(4, 'Delivered', 'Kuala Lumpur Distribution Center', 'Package delivered successfully'),
(4, 'Out for Delivery', 'Kuala Lumpur', 'Package is out for delivery');

-- ============================================
-- Vouchers
-- ============================================
INSERT INTO vouchers (code, name, description, type, value, min_subtotal, max_discount, max_claims, is_shipping_only, is_first_order_only, start_at, end_at, is_active) VALUES
('WELCOME10', 'Welcome Discount', '10% off on your first order', 'percent', 10.00, 100.00, 50.00, 1000, 0, 1, NULL, NULL, 1),
('FREESHIP', 'Free Shipping', 'Free standard shipping on orders above RM200', 'free_shipping', 0.00, 200.00, NULL, NULL, 1, 0, NULL, NULL, 1),
('SAVE50', 'Save RM50', 'RM50 off on orders above RM500', 'amount', 50.00, 500.00, 50.00, 500, 0, 0, NULL, NULL, 1),
('SHIP10', 'Shipping Discount', 'RM10 off shipping fee', 'shipping_amount', 10.00, 100.00, 10.00, NULL, 1, 0, NULL, NULL, 1);

-- ============================================
-- User Vouchers (Sample claimed vouchers)
-- ============================================
INSERT INTO user_vouchers (user_id, voucher_id, status, claimed_at) VALUES
(2, 1, 'active', NOW()),
(3, 2, 'active', NOW()),
(4, 1, 'used', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 3, 'active', NOW());

-- ============================================
-- Saved Addresses
-- ============================================
INSERT INTO saved_addresses (user_id, label, name, phone, address, is_default) VALUES
(2, 'Home', 'Sarah Lee', '013-4567890', '456 Jalan Ampang, Kuala Lumpur, 50450', 1),
(2, 'Office', 'Sarah Lee', '013-4567890', '123 Business Park, Petaling Jaya, 47800', 0),
(3, 'Home', 'Ahmad Rahman', '014-5678901', '789 Taman Desa, Petaling Jaya, 47400', 1),
(4, 'Home', 'Lisa Tan', '015-6789012', '321 Jalan Bukit Bintang, Kuala Lumpur, 55100', 1),
(5, 'Home', 'David Wong', '016-7890123', '654 Taman Tun Dr Ismail, Kuala Lumpur, 60000', 1);
