<?php

namespace App\Utils;

class ErrorHandler {
    public static function respondWithError(int $statusCode, string $message, array $details = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");

        $errorData = [
            "status" => $statusCode,
            "message" => $message,
        ];

        if (getenv('APP_ENV') === 'development') {
            $errorData["details"] = $details;
        }

        error_log("[" . date('Y-m-d H:i:s') . "] ERROR: " . json_encode($errorData) . PHP_EOL, 3, __DIR__ . '/../logs/api-error.log');

        echo json_encode(["error" => $errorData]);
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