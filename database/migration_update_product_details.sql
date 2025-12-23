ALTER TABLE products
ADD COLUMN color VARCHAR(50) AFTER description,
ADD COLUMN size VARCHAR(50) AFTER color,
ADD COLUMN pit_spacing VARCHAR(50) AFTER size,
ADD COLUMN installation_type VARCHAR(50) AFTER pit_spacing,
ADD COLUMN flushing_method VARCHAR(50) AFTER installation_type,
ADD COLUMN bowl_shape VARCHAR(50) AFTER flushing_method,
ADD COLUMN material VARCHAR(100) AFTER bowl_shape,
ADD COLUMN warranty_years INT DEFAULT 2 AFTER material;

UPDATE products SET
    color = 'Pure White',
    size = '660mm × 380mm × 780mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Dual Flush (3L/6L)',
    bowl_shape = 'Round',
    material = 'High-Grade Vitreous China',
    warranty_years = 2,
    description = 'The Elite One-Piece Round Toilet combines contemporary design with superior functionality. Featuring an integrated soft-close seat mechanism, this premium fixture offers exceptional comfort and durability. The seamless one-piece construction ensures easy maintenance while the efficient dual-flush system promotes water conservation without compromising performance.'
WHERE id = 1;

UPDATE products SET
    color = 'Ivory White',
    size = '700mm × 380mm × 800mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Siphon Jet (4.8L)',
    bowl_shape = 'Elongated',
    material = 'Premium Vitreous China',
    warranty_years = 2,
    description = 'Engineered for ultimate comfort, the Deluxe One-Piece Elongated Toilet features an extended bowl design that provides enhanced seating comfort. The powerful siphon jet flushing system ensures thorough bowl cleaning with every flush. Constructed from premium vitreous china with a durable glaze finish that resists staining and maintains its pristine appearance.'
WHERE id = 2;

UPDATE products SET
    color = 'Brilliant White',
    size = '650mm × 370mm × 760mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Gravity Flush (6L)',
    bowl_shape = 'Round',
    material = 'Vitreous China',
    warranty_years = 2,
    description = 'The Classic Two-Piece Round Toilet represents timeless design and reliable performance. Its traditional gravity flush system delivers consistent, powerful flushing action. The two-piece construction allows for easier transportation and installation, making it an ideal choice for both residential and commercial applications.'
WHERE id = 3;

UPDATE products SET
    color = 'Glossy White',
    size = '710mm × 380mm × 785mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Power Flush (4.8L)',
    bowl_shape = 'Elongated',
    material = 'High-Grade Vitreous China',
    warranty_years = 2,
    description = 'Experience superior performance with the Premium Two-Piece Elongated Toilet. Equipped with an advanced power flush system, this fixture ensures complete waste removal while maintaining water efficiency. The elongated bowl provides exceptional comfort, while the high-grade vitreous china construction guarantees long-lasting durability and easy maintenance.'
WHERE id = 4;

UPDATE products SET
    color = 'Matte White',
    size = '530mm × 360mm × 340mm',
    pit_spacing = '180mm / 230mm (Adjustable)',
    installation_type = 'Wall-Mounted',
    flushing_method = 'Concealed Cistern Dual Flush (3L/6L)',
    bowl_shape = 'Round',
    material = 'Premium Ceramic',
    warranty_years = 2,
    description = 'The Modern Wall-Mounted Toilet epitomizes contemporary bathroom design. Its floating installation creates a spacious, minimalist aesthetic while facilitating effortless floor cleaning. The concealed cistern system is integrated within the wall cavity, providing a seamless appearance. The rimless bowl design ensures superior hygiene and simplified maintenance.'
WHERE id = 5;

UPDATE products SET
    color = 'Alpine White',
    size = '520mm × 350mm × 330mm',
    pit_spacing = '180mm / 230mm (Adjustable)',
    installation_type = 'Wall-Mounted',
    flushing_method = 'Concealed Cistern Dual Flush (3L/4.5L)',
    bowl_shape = 'Elongated',
    material = 'Nano-Glaze Ceramic',
    warranty_years = 2,
    description = 'Designed for modern living spaces, the Ultra-Slim Wall-Mounted Toilet combines sleek aesthetics with advanced functionality. The ultra-thin profile maximizes bathroom space while the nano-glaze ceramic surface provides exceptional stain resistance and antibacterial properties. The water-efficient dual flush system significantly reduces water consumption.'
WHERE id = 6;

UPDATE products SET
    color = 'Ceramic White',
    size = '700mm × 400mm × 520mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Automatic Dual Flush (3L/4.5L)',
    bowl_shape = 'Elongated',
    material = 'Antibacterial Ceramic',
    warranty_years = 2,
    description = 'The Smart Bidet Toilet Pro represents the pinnacle of bathroom technology. Features include a heated seat with adjustable temperature settings, warm water bidet with customizable pressure and position, automatic deodorization system, and soft-closing lid. The integrated night light and energy-saving mode enhance user convenience while the self-cleaning nozzle ensures optimal hygiene.'
WHERE id = 7;

UPDATE products SET
    color = 'Pearl White',
    size = '720mm × 410mm × 530mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Intelligent Auto Flush (3L/4.5L)',
    bowl_shape = 'Elongated',
    material = 'Premium Antibacterial Ceramic',
    warranty_years = 2,
    description = 'The Smart Toilet Elite Plus delivers an unparalleled luxury experience with comprehensive automation. Advanced features include hands-free automatic lid opening and closing, personalized user profiles with memory settings, UV sterilization system, built-in air dryer with adjustable temperature, and wireless remote control. The tankless instant heating system provides unlimited warm water supply.'
WHERE id = 8;

UPDATE products SET
    color = 'Bright White',
    size = '610mm × 340mm × 740mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Dual Flush (3L/4.5L)',
    bowl_shape = 'Round',
    material = 'Space-Grade Ceramic',
    warranty_years = 2,
    description = 'Specifically engineered for compact spaces, the Compact Round Toilet delivers full functionality without compromising on quality. The space-efficient design makes it perfect for powder rooms, small bathrooms, or ensuites. Despite its reduced footprint, it maintains excellent flushing performance and comfort, featuring a standard-height seat and efficient water-saving dual flush mechanism.'
WHERE id = 9;

UPDATE products SET
    color = 'Soft White',
    size = '640mm × 360mm × 755mm',
    pit_spacing = '305mm',
    installation_type = 'Floor-Mounted',
    flushing_method = 'Power-Assisted Dual Flush (3L/4.8L)',
    bowl_shape = 'Elongated',
    material = 'High-Density Ceramic',
    warranty_years = 2,
    description = 'The Compact Elongated Toilet offers the perfect balance between space efficiency and comfort. While maintaining a reduced overall footprint, the elongated bowl provides enhanced seating comfort typically found in full-sized models. The power-assisted flushing system ensures reliable performance, and the comfort-height design meets ADA accessibility standards, making it suitable for users of all ages.'
WHERE id = 10;