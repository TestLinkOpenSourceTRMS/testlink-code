-- TestLink 1.9.21 Migration
-- Add milestones table for milestone tracking feature
-- Date: 2026-03-24

CREATE TABLE IF NOT EXISTS `milestones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `testproject_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `due_date` DATE DEFAULT NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completion_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `creation_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_milestone_project` (`testproject_id`),
  KEY `idx_milestone_due_date` (`due_date`),
  KEY `idx_milestone_completed` (`is_completed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
