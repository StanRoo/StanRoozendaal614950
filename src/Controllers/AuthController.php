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

        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userRepository->getUserByUsername($username);

        if (!$user || !password_verify($password, $user->getPassword())) {
            error_log("Login failed for username: $username");
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials"]);
            return;
        }

        $payload = [
            "user_id" => $user->getId(),
            "username" => $user->getUsername(),
            "exp" => time() + (60 * 60) 
        ];
        $jwt = JWT::encode($payload, Config::JWT_SECRET, 'HS256');

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "token" => $jwt
        ]);
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($data['username'], $data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            exit;
        }
    
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $defaultBio = "I love Pokémon :)";
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid email format. Please enter a valid email."]);
            exit;
        }
    
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid username. It can only contain letters, numbers, and underscores, and must be 3-20 characters long."]);
            exit;
        }
    
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid password. It must be at least 8 characters and contain an uppercase letter, a lowercase letter, a number, and a special character."]);
            exit;
        }
    
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        if ($this->userRepository->getUserByEmail($email)) {
            http_response_code(400);
            echo json_encode(["message" => "Email is already registered"]);
            exit;
        }
    
        $newUser = $this->userRepository->createUser($username, $email, $hashedPassword, $defaultBio);
    
        if (!$newUser) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create account"]);
            exit;
        }
    
        echo json_encode(["message" => "Account created successfully!"]);
    }
}