-- Update Schema for 2FA and LMS content types

-- Add 2FA to users
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(255) NULL;

-- Ensure study_materials has content_type and status if missing
-- (The original migration had content_type, but it seems it might not have been applied or is missing in the actual DB)
ALTER TABLE study_materials ADD COLUMN IF NOT EXISTS content_type ENUM('file', 'video', 'link', 'document', 'image') DEFAULT 'file';
ALTER TABLE study_materials ADD COLUMN IF NOT EXISTS status ENUM('active', 'draft', 'archived') DEFAULT 'active';
ALTER TABLE study_materials ADD COLUMN IF NOT EXISTS download_count INT DEFAULT 0;
ALTER TABLE study_materials ADD COLUMN IF NOT EXISTS view_count INT DEFAULT 0;
