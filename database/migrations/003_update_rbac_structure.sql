-- ============================================
-- Migration: Add RBAC Role-Permission Junction
-- ============================================

-- Update permissions table to be standalone (not role-specific)
ALTER TABLE `permissions` DROP FOREIGN KEY `permissions_ibfk_1`;
ALTER TABLE `permissions` DROP COLUMN `role_id`;
ALTER TABLE `permissions` ADD COLUMN `resource` VARCHAR(50) NOT NULL DEFAULT 'general';
ALTER TABLE `permissions` ADD COLUMN `action` VARCHAR(50) NOT NULL DEFAULT 'read';
ALTER TABLE `permissions` ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE;

-- Create role_permissions junction table
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add is_active column to roles table if not exists
ALTER TABLE `roles` ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE;

-- Add deleted_at and is_locked columns to users table if not exists
ALTER TABLE `users` ADD COLUMN `deleted_at` DATETIME NULL;
ALTER TABLE `users` ADD COLUMN `is_locked` BOOLEAN DEFAULT FALSE;
