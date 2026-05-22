<?php
date_default_timezone_set('Asia/Manila');
class Database {
    private static $instance = null;
    private $pdo;

    private $host = 'localhost';
    private $db   = 'uasg_copy_db';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log error in production
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Singleton access
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get raw PDO if needed
    public function getConnection() {
        return $this->pdo;
    }

    // Secure SELECT
    public function select($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

        // Secure SELECT ONE
    public function selectOne($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Secure INSERT/UPDATE/DELETE
    public function execute($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    // Insert and return last insert ID
    public function insert($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }
}
?>