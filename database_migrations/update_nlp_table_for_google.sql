-- Update file_nlp_analysis_tbl for Google Cloud Natural Language API

-- Drop the old table if exists
DROP TABLE IF EXISTS file_nlp_analysis_tbl;

-- Create updated table for Google NLP
CREATE TABLE IF NOT EXISTS file_nlp_analysis_tbl (
    analysis_id INT AUTO_INCREMENT PRIMARY KEY,
    file_upload_id INT NOT NULL,
    
    -- Text extraction data
    extracted_text LONGTEXT DEFAULT NULL,
    word_count INT DEFAULT 0,
    
    -- Category suggestion (based on keyword matching)
    suggested_category VARCHAR(255) DEFAULT NULL,
    category_confidence DECIMAL(5,2) DEFAULT NULL COMMENT 'Confidence score 0-100',
    
    -- Matched keywords (JSON array)
    keywords JSON DEFAULT NULL COMMENT 'Keywords that matched from file_category_key_tbl',
    
    -- Google Cloud NLP Entities (JSON array) - optional if API is used
    entities JSON DEFAULT NULL COMMENT 'Entities extracted by Google NLP API',
    
    -- Full analysis results (JSON)
    full_analysis JSON DEFAULT NULL COMMENT 'Complete analysis result from NLP service',
    
    -- Processing metadata
    processing_time_ms INT DEFAULT NULL,
    
    -- Timestamps
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key
    FOREIGN KEY (file_upload_id) REFERENCES file_upload_tbl(file_upload_id) ON DELETE CASCADE,
    
    -- Indexes for better query performance
    INDEX idx_file_upload (file_upload_id),
    INDEX idx_suggested_category (suggested_category),
    INDEX idx_analyzed_at (analyzed_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE file_nlp_analysis_tbl COMMENT = 'Stores NLP analysis results from Google Cloud Natural Language API';
