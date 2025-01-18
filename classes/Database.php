<?php
class Database
{
    private $conn;
    private static $instance = null;
    private $config;

    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../config/database.php';
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
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . $this->config['host'] .
                    ";dbname=" . $this->config['dbname'] .
                    ";charset=" . $this->config['charset'];

                $this->conn = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }


    private function __clone() {}


    private function __wakeup() {}
}
