CREATE TABLE IF NOT EXISTS notifications_tbl (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(60) NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  related_id INT DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  datetime_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_read (user_id, is_read),
  INDEX idx_created (datetime_created)
);