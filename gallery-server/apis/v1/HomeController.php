<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

class HomeController {
    public static function index() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        
        http_response_code(200);
        echo json_encode([
            "name" => "Gallery API",
            "version" => "1.0.0",
            "description" => "API for managing a photo gallery",
            "endpoints" => [
                "User API" => [
                    "/users/register" => "Register a new user",
                    "/users/login" => "Login an existing user",
                    "/users/profile" => "Get user profile",
                    "/users/update" => "Update user profile"
                ],
                "Image API" => [
                    "/images" => "Get all images with optional filters",
                    "/images/get" => "Get a specific image by ID",
                    "/images/upload" => "Upload a new image",
                    "/images/update" => "Update an existing image",
                    "/images/delete" => "Delete an image"
                ],
                "Tag API" => [
                    "/tags" => "Get all tags",
                    "/tags/image" => "Get tags for a specific image",
                    "/tags/create" => "Create a new tag",
                    "/tags/update" => "Update an existing tag",
                    "/tags/delete" => "Delete a tag"
                ]
            ]
        ]);
    }
}
?>