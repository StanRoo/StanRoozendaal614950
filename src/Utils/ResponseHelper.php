<?php

namespace App\Utils;

class ResponseHelper {
    public static function success(?array $data = null, string $message = "Success", int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
    
        $response = [
            'success' => true,
            'message' => $message,
        ];
    
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
    
        echo json_encode($response);
        exit;
    }

    public static function error(string $message, int $statusCode) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}