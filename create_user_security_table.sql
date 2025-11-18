-- Create table for user security settings (password recovery)

CREATE TABLE IF NOT EXISTS user_security_tbl (
    security_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question1 VARCHAR(255) DEFAULT NULL,
    answer1 VARCHAR(255) DEFAULT NULL,
    question2 VARCHAR(255) DEFAULT NULL,
    answer2 VARCHAR(255) DEFAULT NULL,
    recovery_email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_tbl(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
