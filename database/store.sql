-- ============================================
-- Boutique Store Management System
-- MySQL Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS `boutique_test`;
USE `boutique_test`;

-- ============================================
-- ROLES TABLE (
-- ============================================
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERMISSIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_id` INT NOT NULL,
    `permission` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY (`role_id`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BRANCHES TABLE 
-- ============================================
CREATE TABLE IF NOT EXISTS `branches` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(20),
    `email` VARCHAR(150),
    `manager_id` INT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `role_id` INT NOT NULL,
    `branch_id` INT,
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_login` DATETIME,
    `login_attempts` INT DEFAULT 0,
    `locked_until` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADD FOREIGN KEY FOR BRANCH MANAGER
-- ============================================
ALTER TABLE `branches` ADD CONSTRAINT `fk_branches_manager_id` FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`);

-- ============================================
-- CATEGORIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ITEMS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `sku` VARCHAR(50) UNIQUE NOT NULL,
    `category_id` INT NOT NULL,
    `description` TEXT,
    `cost_price` DECIMAL(10, 2) NOT NULL,
    `selling_price` DECIMAL(10, 2) NOT NULL,
    `reorder_level` INT DEFAULT 10,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    INDEX `idx_sku` (`sku`),
    INDEX `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STOCK TABLE (Inventory per Branch)
-- ============================================
CREATE TABLE IF NOT EXISTS `stock` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `item_id` INT NOT NULL,
    `branch_id` INT NOT NULL,
    `quantity` INT DEFAULT 0,
    `reserved_quantity` INT DEFAULT 0,
    `damaged_quantity` INT DEFAULT 0,
    `last_restock_date` DATE,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
    UNIQUE KEY (`item_id`, `branch_id`),
    INDEX `idx_branch_quantity` (`branch_id`, `quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- STOCK HISTORY TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `stock_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `item_id` INT NOT NULL,
    `branch_id` INT NOT NULL,
    `type` ENUM('in', 'out', 'damage', 'transfer', 'adjustment') NOT NULL,
    `quantity_change` INT NOT NULL,
    `reference_type` VARCHAR(50),
    `reference_id` INT,
    `notes` TEXT,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_reference` (`reference_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRANSFERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `transfers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `item_id` INT NOT NULL,
    `from_branch_id` INT NOT NULL,
    `to_branch_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `status` ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
    `initiated_by` INT NOT NULL,
    `approved_by` INT,
    `transferred_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`initiated_by`) REFERENCES `users`(`id`),
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SALES TRANSACTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `transaction_number` VARCHAR(50) UNIQUE NOT NULL,
    `branch_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `total_amount` DECIMAL(12, 2) NOT NULL,
    `discount_amount` DECIMAL(10, 2) DEFAULT 0,
    `final_amount` DECIMAL(12, 2) NOT NULL,
    `payment_method` ENUM('cash', 'card', 'check', 'other') DEFAULT 'cash',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    INDEX `idx_branch_date` (`branch_id`, `created_at`),
    INDEX `idx_user_date` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SALES ITEMS TABLE (Line items in sales)
-- ============================================
CREATE TABLE IF NOT EXISTS `sales_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `sales_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `subtotal` DECIMAL(12, 2) NOT NULL,
    `discount` DECIMAL(10, 2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sales_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`),
    INDEX `idx_sales` (`sales_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AUDIT LOG TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(100),
    `entity_id` INT,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_user_action` (`user_id`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Manager', 'Full system control with strategic and administrative functions'),
(2, 'Store Keeper', 'Inventory management and stock tracking'),
(3, 'Seller', 'Sales transactions and customer interaction');

-- Insert sample category
INSERT INTO `categories` (`name`, `description`) VALUES
('Clothing', 'Boutique clothing items'),
('Accessories', 'Fashion accessories'),
('Footwear', 'Shoes and footwear');

-- ============================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_branch ON users(branch_id);
CREATE INDEX idx_items_active ON items(is_active);
CREATE INDEX idx_branches_active ON branches(is_active);
CREATE INDEX idx_stock_branch ON stock(branch_id);
CREATE INDEX idx_sales_date ON sales(created_at);

-- ============================================
-- VIEWS FOR REPORTING
-- ============================================

-- Daily Sales Summary
CREATE OR REPLACE VIEW daily_sales_summary AS
SELECT 
    DATE(s.created_at) as sale_date,
    s.branch_id,
    b.name as branch_name,
    s.user_id,
    u.first_name,
    u.last_name,
    COUNT(DISTINCT s.id) as total_transactions,
    SUM(s.final_amount) as total_sales,
    COUNT(DISTINCT si.item_id) as items_sold
FROM sales s
JOIN branches b ON s.branch_id = b.id
JOIN users u ON s.user_id = u.id
LEFT JOIN sales_items si ON s.id = si.sales_id
GROUP BY DATE(s.created_at), s.branch_id, s.user_id;

-- Low Stock Alert View
CREATE OR REPLACE VIEW low_stock_alerts AS
SELECT 
    st.id,
    st.branch_id,
    b.name as branch_name,
    i.id as item_id,
    i.name as item_name,
    i.sku,
    st.quantity,
    i.reorder_level,
    (i.reorder_level - st.quantity) as shortage
FROM stock st
JOIN items i ON st.item_id = i.id
JOIN branches b ON st.branch_id = b.id
WHERE st.quantity <= i.reorder_level
AND i.is_active = TRUE
ORDER BY i.reorder_level - st.quantity DESC;

-- Fast Moving Items View
CREATE OR REPLACE VIEW fast_moving_items AS
SELECT 
    i.id,
    i.name,
    i.sku,
    COUNT(si.id) as times_sold,
    SUM(si.quantity) as total_quantity_sold,
    SUM(si.subtotal) as total_revenue
FROM items i
JOIN sales_items si ON i.id = si.item_id
WHERE si.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY i.id
ORDER BY total_quantity_sold DESC;

