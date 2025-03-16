<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../../models/Tag.php");

class TagController {
    private $tagModel;
    
    public function __construct() {
        $this->tagModel = new Tag();
    }
    
    public function handleOptions() {
        http_response_code(200);
        exit;
    }
    
    public function getTags() {
        // Only allow GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        // Check if we should include image counts
        $withCount = isset($_GET['withCount']) && $_GET['withCount'] === 'true';
        
        // Get all tags
        $tags = $this->tagModel->all($withCount);
        
        $this->sendSuccessResponse(200, [
            "count" => count($tags),
            "tags" => $tags
        ]);
    }
   
    public function getImageTags() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $imageId = isset($_GET['imageId']) ? intval($_GET['imageId']) : 0;
        
        if (!$imageId) {
            $this->sendErrorResponse(400, "Image ID is required");
            return;
        }
        
        $tags = $this->tagModel->getTagsForImage($imageId);
        
        $this->sendSuccessResponse(200, [
            "count" => count($tags),
            "tags" => $tags
        ]);
    }
    
    public function createTag() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['name']) || empty(trim($data['name']))) {
            $this->sendErrorResponse(400, "Tag name is required");
            return;
        }
        
        $tagId = $this->tagModel->findOrCreate($data['name']);
        
        $this->sendSuccessResponse(201, [
            "message" => "Tag created successfully",
            "tagId" => $tagId
        ]);
    }
    
    public function updateTag() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id']) || !isset($data['name']) || empty(trim($data['name']))) {
            $this->sendErrorResponse(400, "Tag ID and name are required");
            return;
        }
        
        if (!$this->tagModel->findById($data['id'])) {
            $this->sendErrorResponse(404, "Tag not found");
            return;
        }
        
        $tag = Tag::create($data['name'], $data['id']);
        $tag->save();
        
        $this->sendSuccessResponse(200, [
            "message" => "Tag updated successfully"
        ]);
    }
 
    public function deleteTag() {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }

        $tagId = $this->getTagIdFromRequest();
        
        if (!$tagId) {
            $this->sendErrorResponse(400, "Tag ID is required");
            return;
        }
        
        if ($this->tagModel->delete($tagId)) {
            $this->sendSuccessResponse(200, [
                "message" => "Tag deleted successfully"
            ]);
        } else {
            $this->sendErrorResponse(404, "Tag not found or already deleted");
        }
    }

    private function sendErrorResponse($statusCode, $message) {
        http_response_code($statusCode);
        echo json_encode(["error" => $message]);
    }

    private function sendSuccessResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode(array_merge(["success" => true], $data));
    }
    
    private function getTagIdFromRequest() {
        if (isset($_GET['id'])) {
            return intval($_GET['id']);
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            return intval($data['id']);
        }
        
        return 0;
    }
}
?>