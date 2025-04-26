<?php

namespace App\Utils;

class ResponseHelper {
    public static function success(array $data = null, string $message = "Success", int $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }

    public static function error(string $message, int $statusCode) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
}