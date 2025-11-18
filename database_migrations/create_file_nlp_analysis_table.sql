-- Create table for storing Lettria NLP analysis results

CREATE TABLE IF NOT EXISTS file_nlp_analysis_tbl (
    analysis_id INT AUTO_INCREMENT PRIMARY KEY,
    file_upload_id INT NOT NULL,
    
    -- Text extraction data
    extracted_text LONGTEXT DEFAULT NULL,
    page_count INT DEFAULT 0,
    word_count INT DEFAULT 0,
    
    -- Language detection
    detected_language VARCHAR(10) DEFAULT NULL,
    language_confidence DECIMAL(5,4) DEFAULT NULL,
    
    -- Sentiment analysis
    sentiment VARCHAR(20) DEFAULT NULL COMMENT 'positive, negative, neutral',
    sentiment_score DECIMAL(5,4) DEFAULT NULL,
    
    -- Classification
    suggested_category VARCHAR(100) DEFAULT NULL,
    category_confidence DECIMAL(5,4) DEFAULT NULL,
    
    -- Keywords (JSON array)
    keywords JSON DEFAULT NULL,
    
    -- Entities (JSON array)
    entities JSON DEFAULT NULL,
    
    -- Topics (JSON array)
    topics JSON DEFAULT NULL,
    
    -- Full analysis results (JSON)
    full_analysis JSON DEFAULT NULL,
    
    -- Processing metadata
    processing_time_ms INT DEFAULT NULL,
    api_version VARCHAR(10) DEFAULT NULL,
    
    -- Timestamps
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key
    FOREIGN KEY (file_upload_id) REFERENCES file_upload_tbl(file_upload_id) ON DELETE CASCADE,
    
    -- Indexes for better query performance
    INDEX idx_file_upload (file_upload_id),
    INDEX idx_sentiment (sentiment),
    INDEX idx_language (detected_language),
    INDEX idx_analyzed_at (analyzed_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to table
ALTER TABLE file_nlp_analysis_tbl COMMENT = 'Stores NLP analysis results from Lettria API for uploaded files';
