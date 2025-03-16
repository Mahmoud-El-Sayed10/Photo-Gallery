<?php

require_once(__DIR__ . "/../connection/connection.php");
require_once(__DIR__ . "/../models/User.php");
require_once(__DIR__ . "/../models/Image.php");
require_once(__DIR__ . "/../models/Tag.php");
require_once(__DIR__ . "/../utils/ImageUtils.php");

// Get database connection
$db = Database::getInstance();
$conn = $db->getConnection();

$uploadsDir = __DIR__ . "/../uploads/images/";
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    echo "Created uploads directory at $uploadsDir\n";
}

// Models
$userModel = new User();
$imageModel = new Image();
$tagModel = new Tag();

// Demo user
$user = User::create("John Doe", "john@example.com", password_hash("password123", PASSWORD_DEFAULT));
$userId = $user->save();
echo "Created user with ID: $userId\n";

// Tags
echo "Creating tags...\n";
$tagIds = [];
$tags = ["Nature", "Architecture", "Portrait", "Travel", "Food", "Abstract"];

foreach ($tags as $tagName) {
    $tag = Tag::create($tagName);
    $tagIds[$tagName] = $tag->save();
    echo "Created tag '$tagName' with ID: {$tagIds[$tagName]}\n";
}

// Sample images data
$sampleImages = [
    [
        "title" => "Sunset at the Beach",
        "description" => "Beautiful sunset captured at the beach",
        "filename" => "Sunset at the Beach.jpg",
        "extension" => "jpg",
        "tags" => ["Nature", "Travel"]
    ],
    [
        "title" => "City Skyline",
        "description" => "Modern skyscrapers in downtown",
        "filename" => "City Skyline.jpg",
        "extension" => "jpg",
        "tags" => ["Architecture", "Travel"]
    ],
    [
        "title" => "Mountain Landscape",
        "description" => "Breathtaking mountain view after hiking",
        "filename" => "Nature Image.jpg",
        "extension" => "jpg",
        "tags" => ["Nature", "Travel"]
    ],
    [
        "title" => "Abstract Art",
        "description" => "Digital abstract artwork with vibrant colors",
        "filename" => "Abstract Art.jpg",
        "extension" => "png",
        "tags" => ["Abstract"]
    ],
    [
        "title" => "Gourmet Dish",
        "description" => "Delicious gourmet dish from a local restaurant",
        "filename" => "Gourmet Dish.jpg",
        "extension" => "jpg",
        "tags" => ["Food"]
    ]
];

// Function to generate placeholder base64 data for demo purposes
function generatePlaceholderBase64($ext) {
    if ($ext == "jpg" || $ext == "jpeg") {
        return "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKAP/2Q==";
    } else {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGBgAAAABQABh6FO1AAAAABJRU5ErkJggg==";
    }
}

// Insert sample images and their tags
foreach ($sampleImages as $image) {
    // Generate base64 data for this image
    $base64Data = generatePlaceholderBase64($image["extension"]);
    
    // Save the base64 encoded image to a file
    $filePath = ImageUtils::saveBase64Image(
        $base64Data, 
        "uploads/images/", 
        $image["filename"]
    );
    
    if ($filePath) {
        echo "Saved image file to: $filePath\n";
        
        // Create the image record
        $imageObj = Image::create(
            $userId,
            $image["title"],
            $image["description"],
            $filePath
        );
        
        $imageId = $imageObj->save();
        echo "Created image '{$image["title"]}' with ID: $imageId\n";
        
        // Associate tags with the image
        foreach ($image["tags"] as $tagName) {
            if (isset($tagIds[$tagName])) {
                $tagId = $tagIds[$tagName];
                $imageModel->addTag($imageId, $tagId);
                echo "Added tag '$tagName' to image '$imageId'\n";
            }
        }
    } else {
        echo "Failed to save image file for: {$image["title"]}\n";
    }
}

echo "Database seeded successfully!\n";
?>