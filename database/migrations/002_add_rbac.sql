-- ============================================
-- Migration: Add Role-Based Access Control (RBAC)
-- Phase 2.2 Implementation
-- ============================================

-- Create roles table if not exists
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create permissions table if not exists
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_id` INT NOT NULL,
    `permission` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_role_permission` (`role_id`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert default roles
-- ============================================
INSERT IGNORE INTO `roles` (`name`, `description`) VALUES
('admin', 'Administrator with full system access'),
('manager', 'Branch manager with management capabilities'),
('staff', 'Staff member with limited access'),
('viewer', 'Read-only access to view data');

-- ============================================
-- Assign default permissions for admin
-- ============================================
INSERT IGNORE INTO `permissions` (`role_id`, `permission`) 
SELECT r.id, p FROM `roles` r, 
(SELECT 'users.create' as p UNION SELECT 'users.read' UNION SELECT 'users.update' UNION SELECT 'users.delete'
 UNION SELECT 'roles.create' UNION SELECT 'roles.read' UNION SELECT 'roles.update' UNION SELECT 'roles.delete'
 UNION SELECT 'permissions.create' UNION SELECT 'permissions.read' UNION SELECT 'permissions.update' UNION SELECT 'permissions.delete'
 UNION SELECT 'items.create' UNION SELECT 'items.read' UNION SELECT 'items.update' UNION SELECT 'items.delete'
 UNION SELECT 'sales.create' UNION SELECT 'sales.read' UNION SELECT 'sales.update' UNION SELECT 'sales.delete'
 UNION SELECT 'reports.view' UNION SELECT 'reports.export'
 UNION SELECT 'branches.create' UNION SELECT 'branches.read' UNION SELECT 'branches.update' UNION SELECT 'branches.delete'
 UNION SELECT 'stock.manage' UNION SELECT 'stock.view') permissions
WHERE r.name = 'admin';

-- ============================================
-- Assign default permissions for manager
-- ============================================
INSERT IGNORE INTO `permissions` (`role_id`, `permission`)
SELECT r.id, p FROM `roles` r,
(SELECT 'users.read' as p UNION SELECT 'users.update'
 UNION SELECT 'items.read' UNION SELECT 'items.update'
 UNION SELECT 'sales.create' UNION SELECT 'sales.read' UNION SELECT 'sales.update'
 UNION SELECT 'reports.view'
 UNION SELECT 'branches.read' UNION SELECT 'branches.update'
 UNION SELECT 'stock.manage' UNION SELECT 'stock.view') permissions
WHERE r.name = 'manager';

-- ============================================
-- Assign default permissions for staff
-- ============================================
INSERT IGNORE INTO `permissions` (`role_id`, `permission`)
SELECT r.id, p FROM `roles` r,
(SELECT 'items.read' as p
 UNION SELECT 'sales.create' UNION SELECT 'sales.read'
 UNION SELECT 'stock.view') permissions
WHERE r.name = 'staff';

-- ============================================
-- Assign default permissions for viewer
-- ============================================
INSERT IGNORE INTO `permissions` (`role_id`, `permission`)
SELECT r.id, p FROM `roles` r,
(SELECT 'items.read' as p
 UNION SELECT 'sales.read'
 UNION SELECT 'reports.view'
 UNION SELECT 'stock.view') permissions
WHERE r.name = 'viewer';
