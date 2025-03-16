<?php

require_once(__DIR__ . "/../connection/connection.php");

$query = "CREATE TABLE IF NOT EXISTS tags(
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";

$result = $conn->query($query);

if($result){
    echo "Tags table created successfully\n";
} else {
    echo "Error creating tags table: " . $conn->error . "\n";
}

?>