<?php

class ImageUtils {

    public static function saveBase64Image($base64Data, $uploadDir = 'uploads/images/', $filename = null) {
        // Extract the image data and type from the base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $imageType = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $base64Data = str_replace(' ', '+', $base64Data);
            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                return false; 
            }

            // Create the upload directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate a unique filename if not provided
            if ($filename === null) {
                $filename = uniqid() . '.' . $imageType;
            } else {
                $filename .= '.' . $imageType;
            }

            $filePath = $uploadDir . $filename;
            
            // Save the image
            if (file_put_contents($filePath, $imageData)) {
                return $filePath;
            }
        }
        
        return false;
    }
    
    public static function deleteImage($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public static function isValidBase64Image($base64Data) {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data)) {
            return false;
        }
        
        $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        $base64Data = str_replace(' ', '+', $base64Data);
        $decodedData = base64_decode($base64Data, true);
        
        return $decodedData !== false;
    }
}
?>