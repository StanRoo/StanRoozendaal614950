<?php

namespace App\Middleware;

use App\Utils\TokenHelper;
use App\Utils\Response;
use App\Repositories\UserRepository;

class AuthMiddleware {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function verifyToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error(401, "Unauthorized: No token provided.");
            return null;
        }
    
        $token = $matches[1];
        $decoded = TokenHelper::decodeToken($token);
    
        if (!$decoded || !isset($decoded->user_id)) {
            Response::error(401, "Unauthorized: Invalid token.");
            return null;
        }

        $user = $this->userRepository->getUserById($decoded->user_id);

        if (!$user) {
            Response::error(401, "Unauthorized: User not found.");
            return null;
        }

        $_SESSION['user'] = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "role" => $user->getRole()
        ];
    
        return $user;
    }
    

    public function requireAdmin() {
        if ($_SESSION['user']['role'] !== 'admin') {
            Response::error(403, "Forbidden: Admin access required.");
            exit();
        }
    }
}
