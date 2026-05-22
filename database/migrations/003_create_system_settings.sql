CREATE TABLE IF NOT EXISTS system_settings_tbl (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO system_settings_tbl (setting_key, setting_value) VALUES
('show_upload_disclaimer', '1'),
('allow_manual_override', '1'),
('override_roles_file_explorer', '["subadmin","member"]'),
('override_roles_batch_upload', '["subadmin","member"]')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
