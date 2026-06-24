<?php
/**
 * Database Configuration
 * Singleton PDO connection for English Club Attendance List
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private $charset = 'utf8mb4';

    /**
     * Resolve DB credentials for the current machine.
     * - Committed defaults below are local-dev friendly and carry NO real password.
     * - Per-machine real credentials live in config/database.local.php (gitignored):
     *   create one on EACH machine (your PC AND the server). The same database.php
     *   then works locally and online with no edits.
     * - Environment variables (DB_HOST/DB_NAME/DB_USER/DB_PASS) override everything.
     */
    private static function config() {
        $cfg = [
            'host'     => 'localhost',
            'dbname'   => 'english_club',
            'username' => 'root',
            'password' => '',
        ];
        if (is_file(__DIR__ . '/database.local.php')) {
            $local = require __DIR__ . '/database.local.php';
            if (is_array($local)) {
                $cfg = array_merge($cfg, $local);
            }
        }
        foreach (['host' => 'DB_HOST', 'dbname' => 'DB_NAME', 'username' => 'DB_USER', 'password' => 'DB_PASS'] as $k => $env) {
            $v = getenv($env);
            if ($v !== false && $v !== '') {
                $cfg[$k] = $v;
            }
        }
        return $cfg;
    }

    private function __construct() {
        $cfg = self::config();
        # Charset is set via the init command below instead of in the DSN,
        # so an unset/empty charset can never trigger "[2019] Unknown character set".
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
        ];

        try {
            $this->connection = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Get singleton instance of Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException("Query failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database instance
 */
function db() {
    return Database::getInstance();
}

