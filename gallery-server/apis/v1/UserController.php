<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../../models/User.php");

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function handleOptions() {
        http_response_code(200);
        exit;
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['fullName']) || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(["error" => "Email already registered"]);
            return;
        }
        
        $user = User::create(
            $data['fullName'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        );
        
        $userId = $user->save();
        
        if ($userId) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "User registered successfully",
                "userId" => $userId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to register user"]);
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }
        
        $userId = $this->userModel->authenticate($data['email'], $data['password']);
        
        if ($userId) {
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $data['email'];
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "userId" => $userId,
                "fullName" => $this->userModel->getFullName(),
                "email" => $this->userModel->getEmail()
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid email or password"]);
        }
    }
    
    public function getProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "User ID is required"]);
            return;
        }
        
        // Find user by ID
        if ($this->userModel->findById($userId)) {
            http_response_code(200);
            echo json_encode([
                "userId" => $userId,
                "fullName" => $this->userModel->getFullName(),
                "email" => $this->userModel->getEmail()
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['userId'])) {
            http_response_code(400);
            echo json_encode(["error" => "User ID is required"]);
            return;
        }
        
        if (!$this->userModel->findById($data['userId'])) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            return;
        }
        
        $fullName = isset($data['fullName']) ? $data['fullName'] : $this->userModel->getFullName();
        $email = isset($data['email']) ? $data['email'] : $this->userModel->getEmail();
        
        if (isset($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            $password = $this->userModel->getPassword();
        }
        
        $user = User::create($fullName, $email, $password, $data['userId']);
        $user->save();
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully"
        ]);
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        session_start();
        session_destroy();
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Logout successful"
        ]);
    }
}
?>