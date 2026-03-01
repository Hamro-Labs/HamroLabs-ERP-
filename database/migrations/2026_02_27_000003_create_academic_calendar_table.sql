-- ============================================================
-- Academic Calendar Table
-- Stores ERP events: exams, holidays, fee dues, batch events, notices
-- ================   ============================================


CREATE TABLE IF NOT EXISTS `academic_calendar` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id`   INT UNSIGNED NOT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `type`        ENUM('exam','holiday','fee','batch','notice') NOT NULL DEFAULT 'notice',
    `start_date`  DATE NOT NULL,
    `end_date`    DATE NOT NULL,
    `batch`       VARCHAR(100) NOT NULL DEFAULT 'All',
    `description` TEXT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_tenant_start` (`tenant_id`, `start_date`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
