ALTER TABLE file_upload_tbl ADD COLUMN is_overridden TINYINT(1) NOT NULL DEFAULT 0 AFTER classification_method;
ALTER TABLE file_upload_tbl ADD COLUMN original_category_tag VARCHAR(100) NULL AFTER is_overridden;
