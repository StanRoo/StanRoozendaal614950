<?php

namespace App\Utils;

class ImageUploader {
    
    private static $uploadDirectory = __DIR__ . "/../../public/uploads/";
    
    public static function upload($image, $folder) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return false;
        }
        
        if ($image['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error: " . $image['error']);
            return false;
        }
        
        $uploadPath = self::$uploadDirectory . $folder . "/";
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                error_log("Failed to create folder: " . $uploadPath);
                return false;
            }
        }

        $uniqueFileName = uniqid() . '.' . $fileExtension;
        $filePath = $uploadPath . $uniqueFileName;

        if (move_uploaded_file($image['tmp_name'], $filePath)) {
            error_log("Failed to move file to: " . $filePath);
            return $folder . '/' . $uniqueFileName;
        }

        return false;
    }
}