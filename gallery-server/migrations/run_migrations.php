
<?php

$host = "localhost";
$user = "root";
$password = "";
$db_name = "gallery_db";

$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->select_db($db_name);

echo "Starting database migrations...\n";

require_once(__DIR__ . "/m001_users.php");
require_once(__DIR__ . "/m002_images.php");
require_once(__DIR__ . "/m003_tags.php");
require_once(__DIR__ . "/m004_image_tags.php");

echo "All migrations completed.\n";

echo "Seeding database...\n";
require_once(__DIR__ . "/../seeds/seeds.php");
echo "Database seeded successfully.\n";

echo "Database setup complete.\n";
?>
