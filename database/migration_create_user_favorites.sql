CREATE TABLE `user_favorites` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    
    -- Ensures a user can't favorite the same product twice
    UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
    
    -- Regular indexes for efficient lookups
    KEY `fk_user_favorites_user_id` (`user_id`),
    KEY `fk_user_favorites_product_id` (`product_id`),
    
    -- Recommended: Foreign Key Constraints for data integrity and automatic clean-up
    CONSTRAINT `fk_user_favorites_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_favorites_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;