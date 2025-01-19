<?php
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );

            // Enable error reporting
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            // Log the connection error
            error_log("Database Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {}
}


define('DB_HOST', 'localhost');
define('DB_NAME', 'youdemy');
define('DB_USER', 'nizar');
define('DB_PASS', 'nizar123');
