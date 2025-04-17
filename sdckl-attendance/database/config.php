<?php
// Database configuration

// Development environment
define('DB_DEV', [
    'host' => 'localhost',
    'dbname' => 'sdckl_attendance',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]);

// Production environment
define('DB_PROD', [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'sdckl_attendance',
    'username' => getenv('DB_USER') ?: 'production_user',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]);

// Database connection class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = (getenv('ENVIRONMENT') === 'production') ? DB_PROD : DB_DEV;
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    // Get database instance (Singleton pattern)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get database connection
    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
}

// Example usage:
/*
try {
    $db = Database::getInstance()->getConnection();
    
    // Example query
    $stmt = $db->prepare("SELECT * FROM students WHERE status = ?");
    $stmt->execute(['active']);
    $students = $stmt->fetchAll();
    
} catch (Exception $e) {
    // Handle error
    error_log($e->getMessage());
    // Show appropriate error message to user
}
*/
?>
