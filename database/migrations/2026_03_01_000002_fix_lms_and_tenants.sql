-- Fix for study_material_categories and tenants settings

-- 1. Tenants settings column
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS settings JSON NULL;

-- 2. Study Material Categories Table
CREATE TABLE IF NOT EXISTS study_material_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) DEFAULT 'fa-folder',
    color VARCHAR(20) DEFAULT '#00B894',
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX (tenant_id),
    INDEX (parent_id),
    INDEX (status),
    INDEX (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Study Materials Table (Ensuring it exists with correct columns)
CREATE TABLE IF NOT EXISTS study_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT NULL,
    file_name VARCHAR(500) NULL,
    file_path VARCHAR(1000) NULL,
    file_type VARCHAR(50) NULL,
    file_size BIGINT UNSIGNED DEFAULT 0,
    file_extension VARCHAR(20) NULL,
    external_url VARCHAR(1000) NULL,
    content_type ENUM('file', 'link', 'video', 'document', 'image') DEFAULT 'file',
    access_type ENUM('public', 'batch', 'student', 'private') DEFAULT 'public',
    visibility ENUM('all', 'specific_batches', 'specific_students') DEFAULT 'all',
    course_id BIGINT UNSIGNED NULL,
    batch_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    tags JSON NULL,
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX (tenant_id),
    INDEX (category_id),
    INDEX (status),
    INDEX (content_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories if table was just created empty
INSERT INTO study_material_categories (tenant_id, name, description, icon, color, sort_order, status)
SELECT 1, 'Notes', 'Class notes and lecture materials', 'fa-file-lines', '#00B894', 1, 'active'
WHERE NOT EXISTS (SELECT 1 FROM study_material_categories WHERE tenant_id = 1 AND name = 'Notes');
