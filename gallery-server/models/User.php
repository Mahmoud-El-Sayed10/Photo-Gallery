<?php

require_once(__DIR__ . "/../interfaces/UserInterface.php");
require_once(__DIR__ . "/../connection/connection.php");

class User implements UserInterface {
    // Instance properties
    private $id;
    private $fullName;
    private $email;
    private $password;
    
    // Database connection
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance();
    }

    public function setData($fullName, $email, $password, $id = null) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        return $this;
    }

    public function save() {
        // If ID is set, update existing user
        if ($this->id) {
            $sql = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $this->db->executeQuery($sql, "sssi", [$this->fullName, $this->email, $this->password, $this->id]);
            $stmt->close();
            return $this->id;
        } 
        // Otherwise insert new user
        else {
            $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $stmt = $this->db->executeQuery($sql, "sss", [$this->fullName, $this->email, $this->password]);
            $stmt->close();
            $this->id = $this->db->getLastInsertId();
            return $this->id;
        }
    }

    public function findById($id) {
        $sql = "SELECT id, full_name, email, password FROM users WHERE id = ?";
        $stmt = $this->db->executeQuery($sql, "i", [$id]);
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->setData(
                $row['full_name'],
                $row['email'],
                $row['password'],
                $row['id']
            );
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    public function findByEmail($email) {
        $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";
        $stmt = $this->db->executeQuery($sql, "s", [$email]);
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->setData(
                $row['full_name'],
                $row['email'],
                $row['password'],
                $row['id']
            );
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    public function all() {
        $sql = "SELECT id, full_name, email FROM users ORDER BY id";
        $stmt = $this->db->executeQuery($sql);
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $stmt->close();
        return $users;
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->executeQuery($sql, "i", [$id]);
        $success = $this->db->getAffectedRows() > 0;
        $stmt->close();
        return $success;
    }

    public function authenticate($email, $password) {
        if ($this->findByEmail($email)) {
            if (password_verify($password, $this->password)) {
                return $this->id;
            }
        }
        
        return false;
    }

    public function getId() {
        return $this->id;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getEmail() {
        return $this->email;
    }
    
    public function getPassword() {
        return $this->password;
    }

    public function clear() {
        $this->id = null;
        $this->fullName = null;
        $this->email = null;
        $this->password = null;
        return $this;
    }

    public static function create($fullName, $email, $password, $id = null, $db = null) {
        $user = new self($db);
        return $user->setData($fullName, $email, $password, $id);
    }
}
?>