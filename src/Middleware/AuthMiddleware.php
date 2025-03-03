<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config;

class AuthMiddleware {
    public static function verifyToken() {
        $headers = apache_request_headers();


        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized: Missing token"]);
            exit;
        }
    
        $token = str_replace("Bearer ", "", $headers['Authorization']);
        
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $decodedArray = (array) $decoded;
    
            error_log(print_r($decodedArray, true));
    
            return $decodedArray;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized: Invalid token"]);
            exit;
        }
    }
}