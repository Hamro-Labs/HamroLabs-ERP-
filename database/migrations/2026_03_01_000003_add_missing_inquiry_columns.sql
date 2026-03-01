-- Migration: Add missing columns to inquiries table
-- Date: 2026-03-01
-- Objective: Fix "Failed to load inquiry list" error for front desk operators

ALTER TABLE inquiries 
ADD COLUMN alt_phone VARCHAR(20) NULL AFTER phone,
ADD COLUMN address TEXT NULL AFTER notes,
ADD COLUMN deleted_at TIMESTAMP NULL AFTER updated_at;

-- Index for optimized filtering
CREATE INDEX idx_inquiries_deleted_tenant ON inquiries(tenant_id, deleted_at);
