<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config;

class AuthMiddleware {
    public static function verifyToken() {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized: No token provided"]);
            exit();
        }

        $jwt = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($jwt, new Key(Config::JWT_SECRET, 'HS256'));
            return (array) $decoded; 
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized: Invalid token"]);
            exit();
        }
    }
}