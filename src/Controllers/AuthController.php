<?php
namespace App\Controllers;

use App\Config;
use Firebase\JWT\JWT;
use App\Repositories\UserRepository;

class AuthController {
    private $userRepository;
    
    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); 
        }

        $data = json_decode(file_get_contents("php://input"), true);

        error_log("Login attempt received.");

        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(400); 
            echo json_encode(["error" => "Username and password are required"]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userRepository->getUserByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            error_log("Login failed for username: $username");
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials"]);
            return;
        }

        $payload = [
            "user_id" => $user['id'],
            "username" => $user['username'],
            "exp" => time() + (60 * 60) 
        ];
        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "token" => $jwt
        ]);
    }
} 
