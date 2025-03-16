<?php

require_once(__DIR__ . "/../interfaces/TagInterface.php");
require_once(__DIR__ . "/../connection/connection.php");

class Tag implements TagInterface {
    private $id;
    private $name;
    
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance();
    }

    public function setData($name, $id = null) {
        $this->id = $id;
        $this->name = $name;
        return $this;
    }

    public function save() {
        if ($this->id) {
            $sql = "UPDATE tags SET name = ? WHERE id = ?";
            $stmt = $this->db->executeQuery($sql, "si", [$this->name, $this->id]);
            $stmt->close();
            return $this->id;
        } 
        else {
            $existingId = $this->findByName($this->name);
            if ($existingId) {
                $this->id = $existingId;
                return $existingId;
            }
            
            $sql = "INSERT INTO tags (name) VALUES (?)";
            $stmt = $this->db->executeQuery($sql, "s", [$this->name]);
            $stmt->close();
            $this->id = $this->db->getLastInsertId();
            return $this->id;
        }
    }

    public function findById($id) {
        $sql = "SELECT id, name FROM tags WHERE id = ?";
        $stmt = $this->db->executeQuery($sql, "i", [$id]);
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->setData($row['name'], $row['id']);
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    public function findByName($name) {
        $sql = "SELECT id, name FROM tags WHERE name = ?";
        $stmt = $this->db->executeQuery($sql, "s", [$name]);
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->setData($row['name'], $row['id']);
            $stmt->close();
            return $row['id'];
        }
        
        $stmt->close();
        return false;
    }

    public function all($withCount = false) {
        if ($withCount) {
            $sql = "
                SELECT t.id, t.name, COUNT(it.image_id) as image_count
                FROM tags t
                LEFT JOIN image_tags it ON t.id = it.tag_id
                GROUP BY t.id
                ORDER BY t.name
            ";
        } else {
            $sql = "SELECT id, name FROM tags ORDER BY name";
        }
        
        $stmt = $this->db->executeQuery($sql);
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        
        $stmt->close();
        return $tags;
    }

    public function delete($id) {
        $sql = "DELETE FROM tags WHERE id = ?";
        $stmt = $this->db->executeQuery($sql, "i", [$id]);
        $success = $this->db->getAffectedRows() > 0;
        $stmt->close();
        return $success;
    }

    public function findOrCreate($name) {
        $tagId = $this->findByName($name);
        
        if (!$tagId) {
            $this->setData($name);
            $tagId = $this->save();
        }
        
        return $tagId;
    }

    public function getTagsForImage($imageId) {
        $sql = "
            SELECT t.id, t.name
            FROM tags t
            JOIN image_tags it ON t.id = it.tag_id
            WHERE it.image_id = ?
            ORDER BY t.name
        ";
        $stmt = $this->db->executeQuery($sql, "i", [$imageId]);
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        
        $stmt->close();
        return $tags;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function clear() {
        $this->id = null;
        $this->name = null;
        return $this;
    }

    public static function create($name, $id = null, $db = null) {
        $tag = new self($db);
        return $tag->setData($name, $id);
    }
}
?>