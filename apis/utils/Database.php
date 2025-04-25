<?php
/**
 * @file apis/utils/Database.php
 * @brief defination of Database class, used to connect to database.
 * @author xingfen
 * @date 2025-04-13
 */

namespace App\Database;

class Database {
    private $host = 'localhost';
    private $db_name = 'yiliao2';
    private $username = 'root';
    private $password = '3.1415926';
    private $conn;

    /* connect the database and return the PDO instance */
    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, /* set error mode to exception */
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, /* set default fetch mode to associative array */
                \PDO::ATTR_EMULATE_PREPARES => false, /* disable emulation of prepared statements */
            ];
            $this->conn = new \PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /* connect to the database server without specifying a database */
    public function connectToServer() {
        try {
            $dsn = "mysql:host={$this->host};charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, /* set error mode to exception */
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, /* set default fetch mode to associative array */
                \PDO::ATTR_EMULATE_PREPARES => false, /* disable emulation of prepared statements */
            ];
            $this->conn = new \PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch (\PDOException $e) {
            throw new \Exception("Database server connection failed: " . $e->getMessage());
        }
    }

    /* 获取链接参数 */
    public function getConnectionParams() {
        return [
            'host' => $this->host,
            'db_name' => $this->db_name,
            'username' => $this->username,
            'password' => $this->password
        ];
    }
}
