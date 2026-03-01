-- Study Materials Module Migration
-- Creates tables for managing study materials, categories, and access tracking

-- 1. Study Material Categories Table
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

-- 2. Study Materials Table
CREATE TABLE IF NOT EXISTS study_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT NULL,
    
    -- File Information
    file_name VARCHAR(500) NULL,
    file_path VARCHAR(1000) NULL,
    file_type VARCHAR(50) NULL,
    file_size BIGINT UNSIGNED DEFAULT 0,
    file_extension VARCHAR(20) NULL,
    
    -- External Link Support
    external_url VARCHAR(1000) NULL,
    content_type ENUM('file', 'link', 'video', 'document', 'image') DEFAULT 'file',
    
    -- Access Control
    access_type ENUM('public', 'batch', 'student', 'private') DEFAULT 'public',
    visibility ENUM('all', 'specific_batches', 'specific_students') DEFAULT 'all',
    
    -- Academic Context
    course_id BIGINT UNSIGNED NULL,
    batch_id BIGINT UNSIGNED NULL,
    subject_id BIGINT UNSIGNED NULL,
    
    -- Metadata
    tags JSON NULL,
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    
    -- Status & Sorting
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    
    -- Audit
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX (tenant_id),
    INDEX (category_id),
    INDEX (course_id),
    INDEX (batch_id),
    INDEX (subject_id),
    INDEX (status),
    INDEX (content_type),
    INDEX (access_type),
    INDEX (is_featured),
    INDEX (sort_order),
    INDEX (published_at),
    FULLTEXT INDEX ft_title_desc (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Study Material Access Permissions (for specific batches/students)
CREATE TABLE IF NOT EXISTS study_material_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    entity_type ENUM('batch', 'student') NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    can_view BOOLEAN DEFAULT TRUE,
    can_download BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (tenant_id),
    INDEX (material_id),
    INDEX (entity_type, entity_id),
    UNIQUE KEY unique_permission (material_id, entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Study Material Access Logs
CREATE TABLE IF NOT EXISTS study_material_access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    user_type ENUM('student', 'teacher', 'admin') NOT NULL,
    action ENUM('view', 'download') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (tenant_id),
    INDEX (material_id),
    INDEX (user_id),
    INDEX (action),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Study Material Favorites (for students)
CREATE TABLE IF NOT EXISTS study_material_favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (tenant_id),
    INDEX (student_id),
    INDEX (material_id),
    UNIQUE KEY unique_favorite (tenant_id, material_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Study Material Feedback/Ratings
CREATE TABLE IF NOT EXISTS study_material_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (tenant_id),
    INDEX (material_id),
    INDEX (student_id),
    INDEX (rating),
    UNIQUE KEY unique_feedback (tenant_id, material_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO study_material_categories (tenant_id, name, description, icon, color, sort_order, status) VALUES
(1, 'Notes', 'Class notes and lecture materials', 'fa-file-lines', '#00B894', 1, 'active'),
(1, 'PDF Documents', 'PDF files and documents', 'fa-file-pdf', '#E74C3C', 2, 'active'),
(1, 'Video Lectures', 'Recorded video lectures and tutorials', 'fa-video', '#9B59B6', 3, 'active'),
(1, 'Assignments', 'Practice assignments and homework', 'fa-pen-to-square', '#F39C12', 4, 'active'),
(1, 'Previous Questions', 'Past exam papers and questions', 'fa-clipboard-question', '#3498DB', 5, 'active'),
(1, 'Reference Books', 'Recommended reference materials', 'fa-book', '#1ABC9C', 6, 'active'),
(1, 'Important Links', 'Useful external resources', 'fa-link', '#E67E22', 7, 'active'),
(1, 'Syllabus', 'Course syllabus and curriculum', 'fa-list-check', '#34495E', 8, 'active');
