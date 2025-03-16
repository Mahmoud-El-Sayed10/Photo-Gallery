<?php

require_once(__DIR__ . "/../connection/connection.php");

$query = "CREATE TABLE IF NOT EXISTS images(
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            file_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)";

$result = $conn->query($query);

if ($result){
    echo "Images table created successfully\n";
} else {
    echo "Error creating images table: " . $conn->error . "\n";
}

?>