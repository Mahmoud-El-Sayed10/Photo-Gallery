<?php

require_once(__DIR__ . "/../interfaces/ImageInterface.php");
require_once(__DIR__ . "/../connection/connection.php");
require_once(__DIR__ . "/../utils/ImageUtils.php");

class Image implements ImageInterface {
    private $id;
    private $userId;
    private $title;
    private $description;
    private $filePath;

    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance();
    }

    public function setData($userId, $title, $description, $filePath, $id = null) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->filePath = $filePath;
        return $this;
    }


    public function save() {
        if ($this->id) {
            $sql = "UPDATE images SET user_id = ?, title = ?, description = ?, file_path = ? WHERE id = ?";
            $stmt = $this->db->executeQuery($sql, "isssi", [
                $this->userId, 
                $this->title, 
                $this->description,
                $this->filePath,
                $this->id
            ]);
            $stmt->close();
            return $this->id;
        } 
        else {
            $sql = "INSERT INTO images (user_id, title, description, file_path) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->executeQuery($sql, "isss", [
                $this->userId, 
                $this->title, 
                $this->description,
                $this->filePath
            ]);
            $stmt->close();
            $this->id = $this->db->getLastInsertId();
            return $this->id;
        }
    }

    public function findById($id) {
        $sql = "SELECT id, user_id, title, description, file_path FROM images WHERE id = ?";
        $stmt = $this->db->executeQuery($sql, "i", [$id]);
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->setData(
                $row['user_id'],
                $row['title'],
                $row['description'],
                $row['file_path'],
                $row['id']
            );
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    public function all($filters = []) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT i.id, i.user_id, i.title, i.description, i.file_path, 
                   i.created_at, i.updated_at, u.full_name as user_name
            FROM images i
            JOIN users u ON i.user_id = u.id
        ";
        
        $whereConditions = [];
        $params = [];
        $types = "";
        
        // Add tag filter if specified
        if (isset($filters['tag_id'])) {
            $sql .= " JOIN image_tags it ON i.id = it.image_id";
            $whereConditions[] = "it.tag_id = ?";
            $params[] = intval($filters['tag_id']);
            $types .= "i";
        }
        
        // Add user filter if specified
        if (isset($filters['user_id'])) {
            $whereConditions[] = "i.user_id = ?";
            $params[] = intval($filters['user_id']);
            $types .= "i";
        }
        
        // Add search filter if specified
        if (isset($filters['search']) && !empty($filters['search'])) {
            $whereConditions[] = "(i.title LIKE ? OR i.description LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $this->db->executeQuery($sql, $types, $params);
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $row['tags'] = $this->getImageTags($row['id']);
            $images[] = $row;
        }
        
        $stmt->close();
        return $images;
    }
    
    public function delete($id) {
        if ($this->findById($id)) {
            $filePath = $this->filePath;
            
            $sql = "DELETE FROM images WHERE id = ?";
            $stmt = $this->db->executeQuery($sql, "i", [$id]);
            $success = $this->db->getAffectedRows() > 0;
            $stmt->close();
            
            if ($success) {
                if ($filePath && file_exists($filePath)) {
                    ImageUtils::deleteImage($filePath);
                }
                return true;
            }
        }
        
        return false;
    }

    public function getImageTags($imageId) {
        $sql = "
            SELECT t.id, t.name
            FROM tags t
            JOIN image_tags it ON t.id = it.tag_id
            WHERE it.image_id = ?
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

    public function addTag($imageId, $tagId) {
        $sql = "INSERT IGNORE INTO image_tags (image_id, tag_id) VALUES (?, ?)";
        $stmt = $this->db->executeQuery($sql, "ii", [$imageId, $tagId]);
        $success = $this->db->getAffectedRows() > 0;
        $stmt->close();
        return $success;
    }

    public function removeTag($imageId, $tagId) {
        $sql = "DELETE FROM image_tags WHERE image_id = ? AND tag_id = ?";
        $stmt = $this->db->executeQuery($sql, "ii", [$imageId, $tagId]);
        $success = $this->db->getAffectedRows() > 0;
        $stmt->close();
        return $success;
    }

    public function createFromBase64($userId, $title, $description, $base64Data) {
        if (!ImageUtils::isValidBase64Image($base64Data)) {
            return false;
        }
    
        $uploadDir = 'uploads/images/';
        $filePath = ImageUtils::saveBase64Image($base64Data, $uploadDir);
        
        if (!$filePath) {
            return false;
        }
        
        $this->setData($userId, $title, $description, $filePath);
        return $this->save();
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getFilePath() {
        return $this->filePath;
    }

    public function clear() {
        $this->id = null;
        $this->userId = null;
        $this->title = null;
        $this->description = null;
        $this->filePath = null;
        return $this;
    }
    
    public static function create($userId, $title, $description, $filePath, $id = null, $db = null) {
        $image = new self($db);
        return $image->setData($userId, $title, $description, $filePath, $id);
    }
}
?>