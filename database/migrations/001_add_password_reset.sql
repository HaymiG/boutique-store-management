-- ============================================
-- Database Migration - Add Password Reset
-- Run this after the initial schema
-- ============================================

-- Add password reset columns to users table
ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) NULL UNIQUE AFTER `locked_until`;
ALTER TABLE `users` ADD COLUMN `reset_token_expires` DATETIME NULL AFTER `reset_token`;

-- Add index for soft delete queries
ALTER TABLE `users` ADD INDEX `idx_is_active` (`is_active`);
ALTER TABLE `users` ADD INDEX `idx_email` (`email`);
