<?php

namespace App\Utils;

class ErrorHandler {
    public static function respondWithError(int $statusCode, string $message): void {
        http_response_code($statusCode);
        echo json_encode(["error" => $message]);
        exit;
    }
}