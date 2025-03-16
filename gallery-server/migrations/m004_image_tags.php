<?php

require_once(__DIR__ . "/../connection/connection.php");

$query = "CREATE TABLE IF NOT EXISTS image_tags(
        image_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (image_id, tag_id),
        FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE)";


$result = $conn->query($query);

if($result){
    echo "Image_tags junction table created successfully\n";
} else {
    echo "Error creating image_tags junction table: " . $conn->error . "\n";
}

?>