-- Teacher Subject Allocation Migrations

-- 1. Create Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX (tenant_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Update batch_subject_allocations to use subject_id
-- We first check if subject column exists before trying to rename/modify
-- Since we are starting fresh with no data (confirmed in harvest), we can safely reconstruct or modify.

-- Check if we need to drop the old table and recreate for clean structure if it was varchar based
-- Based on previous DESCRIBE: batch_subject_allocations had (id, tenant_id, batch_id, teacher_id, subject)
-- We will migrate it to use subject_id.

ALTER TABLE batch_subject_allocations ADD COLUMN subject_id BIGINT UNSIGNED NOT NULL AFTER teacher_id;
ALTER TABLE batch_subject_allocations ADD INDEX (subject_id);
ALTER TABLE batch_subject_allocations DROP COLUMN subject;

-- 3. Add foreign key constraints (optional but good for integrity)
-- ALTER TABLE batch_subject_allocations ADD CONSTRAINT fk_allocation_subject FOREIGN KEY (subject_id) REFERENCES subjects(id);
