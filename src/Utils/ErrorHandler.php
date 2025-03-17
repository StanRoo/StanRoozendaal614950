<?php

namespace App\Utils;

class ErrorHandler {
    public static function respondWithError(int $statusCode, string $message, array $details = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $errorData = [
            "status" => $statusCode,
            "message" => $message,
            "details" => $details
        ];
        
        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . json_encode($errorData) . PHP_EOL, 3, __DIR__ . '/../api-error.log');

        echo json_encode([
            "error" => $message,
            "code" => $statusCode,
            "details" => $details
        ]);

        exit;
    }

    public static function handleException(\Throwable $exception): void {
        self::respondWithError(500, "Internal Server Error", [
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
}