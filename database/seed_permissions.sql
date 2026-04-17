-- ============================================
-- Boutique Store Management System
-- Seed Permissions & Default Admin User
-- Run this AFTER store.sql
-- ============================================

-- ============================================
-- SEED PERMISSIONS FOR EACH ROLE
-- ============================================

-- Manager Permissions (role_id = 1)
INSERT IGNORE INTO `permissions` (`role_id`, `permission`) VALUES
(1, 'branch.create'),
(1, 'branch.read'),
(1, 'branch.update'),
(1, 'branch.delete'),
(1, 'user.create'),
(1, 'user.read'),
(1, 'user.update'),
(1, 'user.delete'),
(1, 'inventory.create'),
(1, 'inventory.read'),
(1, 'inventory.update'),
(1, 'inventory.delete'),
(1, 'inventory.transfer'),
(1, 'sales.read'),
(1, 'sales.report'),
(1, 'report.generate');

-- Store Keeper Permissions (role_id = 2)
INSERT IGNORE INTO `permissions` (`role_id`, `permission`) VALUES
(2, 'inventory.create'),
(2, 'inventory.read'),
(2, 'inventory.update'),
(2, 'stock.manage'),
(2, 'sales.read.own'),
(2, 'report.inventory'),
(2, 'report.alerts');

-- Seller Permissions (role_id = 3)
INSERT IGNORE INTO `permissions` (`role_id`, `permission`) VALUES
(3, 'sales.create'),
(3, 'sales.read.own'),
(3, 'inventory.read');

-- ============================================
-- SEED DEFAULT USERS
-- Passwords are all: Password123
-- bcrypt hash: $2y$12$ZGRYiPpgfbShruUQ1gqT.ePlmeTThd.9TsR9WiUNlipPNo0mxYRGS
-- ============================================

-- Create a default branch first
INSERT IGNORE INTO `branches` (`id`, `name`, `address`, `phone`, `email`, `is_active`) VALUES
(1, 'Downtown Boutique', '123 Main Street, Downtown', '+1-555-0100', 'downtown@boutique.com', TRUE),
(2, 'Uptown Premium', '456 Oak Avenue, Uptown', '+1-555-0200', 'uptown@boutique.com', TRUE);

-- Insert default admin user (Manager)
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role_id`, `branch_id`, `is_active`) VALUES
(1, 'admin', 'admin@boutique.com', '$2y$12$ZGRYiPpgfbShruUQ1gqT.ePlmeTThd.9TsR9WiUNlipPNo0mxYRGS', 'Admin', 'Master', '+1-555-0001', 1, 1, TRUE);

-- Insert default store keeper
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role_id`, `branch_id`, `is_active`) VALUES
(2, 'storekeeper', 'store@boutique.com', '$2y$12$ZGRYiPpgfbShruUQ1gqT.ePlmeTThd.9TsR9WiUNlipPNo0mxYRGS', 'Alice', 'Smith', '+1-555-0002', 2, 1, TRUE);

-- Insert default seller
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role_id`, `branch_id`, `is_active`) VALUES
(3, 'seller', 'seller@boutique.com', '$2y$12$ZGRYiPpgfbShruUQ1gqT.ePlmeTThd.9TsR9WiUNlipPNo0mxYRGS', 'Sarah', 'Connor', '+1-555-0003', 3, 1, TRUE);

-- Update branch manager references
UPDATE `branches` SET `manager_id` = 1 WHERE `id` = 1;
UPDATE `branches` SET `manager_id` = 1 WHERE `id` = 2;

-- ============================================
-- VERIFY SEED DATA
-- ============================================
-- SELECT u.id, u.username, u.email, r.name as role FROM users u JOIN roles r ON u.role_id = r.id;
-- SELECT r.name, COUNT(p.id) as permissions FROM roles r LEFT JOIN permissions p ON r.id = p.role_id GROUP BY r.id;
