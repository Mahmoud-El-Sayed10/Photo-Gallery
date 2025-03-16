<?php
// Add CORS headers for all requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Defining base directory
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove the base directory from the request if present
if (strpos($request, $base_dir) === 0) {
    $request = substr($request, strlen($base_dir));
}

// Ensure the request is at least '/'
if ($request == '') {
    $request = '/';
}

// Define API routes
$apis = [
    // User routes
    '/users/register'      => ['controller' => 'UserController', 'method' => 'register'],
    '/users/login'         => ['controller' => 'UserController', 'method' => 'login'],
    '/users/profile'       => ['controller' => 'UserController', 'method' => 'getProfile'],
    '/users/update'        => ['controller' => 'UserController', 'method' => 'updateProfile'],
    
    // Image routes
    '/images'              => ['controller' => 'ImageController', 'method' => 'getImages'],
    '/images/get'          => ['controller' => 'ImageController', 'method' => 'getImage'],
    '/images/upload'       => ['controller' => 'ImageController', 'method' => 'uploadImage'],
    '/images/update'       => ['controller' => 'ImageController', 'method' => 'updateImage'],
    '/images/delete'       => ['controller' => 'ImageController', 'method' => 'deleteImage'],
    
    // Tag routes
    '/tags'                => ['controller' => 'TagController', 'method' => 'getTags'],
    '/tags/image'          => ['controller' => 'TagController', 'method' => 'getImageTags'],
    '/tags/create'         => ['controller' => 'TagController', 'method' => 'createTag'],
    '/tags/update'         => ['controller' => 'TagController', 'method' => 'updateTag'],
    '/tags/delete'         => ['controller' => 'TagController', 'method' => 'deleteTag'],
    
    // Home route
    '/'                    => ['controller' => 'HomeController', 'method' => 'index']
];

// Check if the route exists
if (isset($apis[$request])) {
    $controllerName = $apis[$request]['controller'];
    $method = $apis[$request]['method'];
    
    // Include the controller file
    $controllerFile = __DIR__ . "/apis/v1/{$controllerName}.php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        // Create controller instance and call the method
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Method {$method} not found in {$controllerName}"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Controller file not found: {$controllerFile}"]);
    }
} else {
    // Route not found
    http_response_code(404);
    echo json_encode(["error" => "API endpoint not found: {$request}"]);
}
?>