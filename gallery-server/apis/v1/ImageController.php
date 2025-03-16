<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../../models/Image.php");
require_once(__DIR__ . "/../../models/Tag.php");
require_once(__DIR__ . "/../../utils/ImageUtils.php");

class ImageController {
    private $imageModel;
    private $tagModel;
    private $baseUrl;
    
    public function __construct() {
        $this->imageModel = new Image();
        $this->tagModel = new Tag();
        
        // Set the base URL for image paths
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $this->baseUrl = $protocol . $host . '/Gallery-System/gallery-server/';
    }
    
    // Convert relative path to absolute URL
    private function getFullImageUrl($relativePath) {
        if (empty($relativePath)) {
            return '';
        }
        
        // Check if the path is already an absolute URL
        if (strpos($relativePath, 'http://') === 0 || strpos($relativePath, 'https://') === 0) {
            return $relativePath;
        }
        
        return $this->baseUrl . $relativePath;
    }
    
    public function handleOptions() {
        http_response_code(200);
        exit;
    }
    
    public function getImages() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $filters = $this->buildFiltersFromQueryParams();
        
        $images = $this->imageModel->all($filters);
        
        // Add full URLs to all images
        foreach ($images as &$image) {
            if (isset($image['file_path'])) {
                $image['file_path'] = $this->getFullImageUrl($image['file_path']);
            }
        }
        
        $this->sendSuccessResponse(200, [
            "count" => count($images),
            "images" => $images
        ]);
    }
    
    public function getImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $imageId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$imageId) {
            $this->sendErrorResponse(400, "Image ID is required");
            return;
        }
        
        if ($this->imageModel->findById($imageId)) {
            $tags = $this->imageModel->getImageTags($imageId);
            
            $this->sendSuccessResponse(200, [
                "image" => [
                    "id" => $imageId,
                    "user_id" => $this->imageModel->getUserId(),
                    "title" => $this->imageModel->getTitle(),
                    "description" => $this->imageModel->getDescription(),
                    "file_path" => $this->getFullImageUrl($this->imageModel->getFilePath()),
                    "tags" => $tags
                ]
            ]);
        } else {
            $this->sendErrorResponse(404, "Image not found");
        }
    }
    
    /**
     * Upload a new image
     */
    public function uploadImage() {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        // Get JSON data from request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate required fields
        if (!$this->validateUploadData($data)) {
            return;
        }
        
        // Create image from base64 data
        $imageId = $this->imageModel->createFromBase64(
            $data['userId'],
            $data['title'],
            isset($data['description']) ? $data['description'] : '',
            $data['base64Image']
        );
        
        if ($imageId) {
            // Process tags if provided
            $this->processImageTags($imageId, $data);
            
            $this->sendSuccessResponse(201, [
                "message" => "Image uploaded successfully",
                "imageId" => $imageId
            ]);
        } else {
            $this->sendErrorResponse(500, "Failed to upload image");
        }
    }

    public function updateImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendErrorResponse(400, "Image ID is required");
            return;
        }
        
        if (!$this->imageModel->findById($data['id'])) {
            $this->sendErrorResponse(404, "Image not found");
            return;
        }
        
        $filePath = $this->updateImageFile($data);
        if ($filePath === false) {
            return;
        }
        
        $this->updateImageRecord($data, $filePath);
        
        $this->updateImageTags($data);
        
        $this->sendSuccessResponse(200, [
            "message" => "Image updated successfully"
        ]);
    }
    
    public function deleteImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendErrorResponse(405, "Method not allowed");
            return;
        }
        
        $imageId = $this->getImageIdFromRequest();
        
        if (!$imageId) {
            $this->sendErrorResponse(400, "Image ID is required");
            return;
        }
        
        if ($this->imageModel->delete($imageId)) {
            $this->sendSuccessResponse(200, [
                "message" => "Image deleted successfully"
            ]);
        } else {
            $this->sendErrorResponse(404, "Image not found or already deleted");
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
 
    private function buildFiltersFromQueryParams() {
        $filters = [];
        
        if (isset($_GET['tag_id']) && $_GET['tag_id']) {
            $filters['tag_id'] = intval($_GET['tag_id']);
        }
        
        if (isset($_GET['user_id']) && $_GET['user_id']) {
            $filters['user_id'] = intval($_GET['user_id']);
        }
        
        if (isset($_GET['search']) && $_GET['search']) {
            $filters['search'] = $_GET['search'];
        }
        
        return $filters;
    }
    
    private function validateUploadData($data) {
        if (!isset($data['userId']) || !isset($data['title']) || !isset($data['base64Image'])) {
            $this->sendErrorResponse(400, "Missing required fields");
            return false;
        }
        
        return true;
    }

    private function processImageTags($imageId, $data) {
        if (isset($data['tags']) && is_array($data['tags']) && !empty($data['tags'])) {
            foreach ($data['tags'] as $tagName) {
                $tagId = $this->tagModel->findOrCreate($tagName);
                $this->imageModel->addTag($imageId, $tagId);
            }
        }
    }

    private function updateImageFile($data) {
        $filePath = $this->imageModel->getFilePath();
        
        if (isset($data['base64Image']) && !empty($data['base64Image'])) {
            if ($filePath && file_exists($filePath)) {
                ImageUtils::deleteImage($filePath);
            }
            
            $filePath = ImageUtils::saveBase64Image($data['base64Image'], 'uploads/images/');
            if (!$filePath) {
                $this->sendErrorResponse(500, "Failed to save new image");
                return false;
            }
        }
        
        return $filePath;
    }
    
    private function updateImageRecord($data, $filePath) {
        $userId = $this->imageModel->getUserId();
        $title = isset($data['title']) ? $data['title'] : $this->imageModel->getTitle();
        $description = isset($data['description']) ? $data['description'] : $this->imageModel->getDescription();
        
        $image = Image::create($userId, $title, $description, $filePath, $data['id']);
        $image->save();
    }

    private function updateImageTags($data) {
        if (isset($data['tags']) && is_array($data['tags'])) {
            $currentTags = $this->imageModel->getImageTags($data['id']);
            $currentTagIds = array_column($currentTags, 'id');
            
            $newTagIds = [];
            foreach ($data['tags'] as $tagName) {
                $tagId = $this->tagModel->findOrCreate($tagName);
                $newTagIds[] = $tagId;
                
                if (!in_array($tagId, $currentTagIds)) {
                    $this->imageModel->addTag($data['id'], $tagId);
                }
            }
            
            foreach ($currentTagIds as $tagId) {
                if (!in_array($tagId, $newTagIds)) {
                    $this->imageModel->removeTag($data['id'], $tagId);
                }
            }
        }
    }

    private function getImageIdFromRequest() {
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