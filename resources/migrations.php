<?php
/**
 * Auto-migration runner
 * Runs pending SQL migrations on page load.
 * Include this in your auth check or session file.
 */
class MigrationRunner {
    private $db;
    private $migrationsDir;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsDir = __DIR__ . '/../database/migrations/';
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable() {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS migrations_tbl (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function runPending() {
        try {
            if (!is_dir($this->migrationsDir)) return;
            $files = glob($this->migrationsDir . '*.sql');
            sort($files);

            foreach ($files as $file) {
                $filename = basename($file);
                $ran = $this->db->select("SELECT id FROM migrations_tbl WHERE filename = ?", [$filename]);
                if (!empty($ran)) continue;

                $sql = file_get_contents($file);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) $this->db->execute($stmt);
                }
                $this->db->execute("INSERT INTO migrations_tbl (filename) VALUES (?)", [$filename]);
            }
        } catch (Exception $e) {
            // Silently fail - don't break the app
        }
    }
}
