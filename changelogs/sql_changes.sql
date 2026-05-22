-- ============================================================
-- UASG DATABASE CHANGES
-- Run these on the client's WAMP MySQL database
-- ============================================================

-- Fix prediction_confidence column overflow
-- App passes confidence as percentage (e.g. 44.11) but column only accepted 0-9.9999
ALTER TABLE ml_incremental_training_queue_tbl 
MODIFY COLUMN prediction_confidence decimal(5,2) NULL;

-- ============================================================
-- UPDATE 2 — Manual Classification Override
-- Date: 2026-05-21
-- ============================================================

-- Track when user manually overrides ML classification
ALTER TABLE file_upload_tbl ADD COLUMN is_overridden TINYINT(1) NOT NULL DEFAULT 0 AFTER classification_method;
ALTER TABLE file_upload_tbl ADD COLUMN original_category_tag VARCHAR(100) NULL AFTER is_overridden;

-- ============================================================
-- UPDATE 3 — System Settings Table
-- Date: 2026-05-21
-- ============================================================

CREATE TABLE IF NOT EXISTS system_settings_tbl (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO system_settings_tbl (setting_key, setting_value) VALUES
('show_upload_disclaimer', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add allow_manual_override setting (default enabled)
INSERT INTO system_settings_tbl (setting_key, setting_value) VALUES
('allow_manual_override', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Role-based override settings (default: all roles allowed)
INSERT INTO system_settings_tbl (setting_key, setting_value) VALUES
('override_roles_file_explorer', '["subadmin","member"]'),
('override_roles_batch_upload', '["subadmin","member"]')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
