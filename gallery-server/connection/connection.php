<?php

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $host = "localhost";
        $user = "root";
        $password = "";
        $db_name = "gallery_db";
        
        $this->conn = new mysqli($host, $user, $password, $db_name);
        
        if ($this->conn->connect_error) {
            http_response_code(500);
            die(json_encode([
                "status" => "error",
                "message" => "Database connection failed: " . $this->conn->connect_error
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function executeQuery($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }
    
    public function closeConnection() {
        $this->conn->close();
    }
    
    private function __clone() {}
    
    public function __wakeup() {}
}
?>