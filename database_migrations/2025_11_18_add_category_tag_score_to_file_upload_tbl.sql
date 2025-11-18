-- Add category_tag and category_score to file_upload_tbl
ALTER TABLE file_upload_tbl
  ADD COLUMN category_tag VARCHAR(255) DEFAULT NULL AFTER file_category_id,
  ADD COLUMN category_score DECIMAL(5,2) DEFAULT NULL AFTER category_tag;

-- Example usage:
-- category_tag: NLP-assigned category name (e.g., 'Resolutions')
-- category_score: NLP confidence/score (e.g., 85.50)
