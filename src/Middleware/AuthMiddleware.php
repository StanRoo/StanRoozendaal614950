<?php

namespace App\Middleware;

use App\Utils\TokenHelper;
use App\Utils\ErrorHandler;
use App\Repositories\UserRepository;
use App\Config;

class AuthMiddleware {
    private $userRepository;

    // Constructor to receive the UserRepository via Dependency Injection
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    // Verify the token and return the authenticated user
    public function verifyToken() {
        try {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? '';

            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                ErrorHandler::respondWithError(401, "Unauthorized: No token provided.");
            }

            $token = $matches[1];
            $decoded = TokenHelper::decodeToken($token);

            if (!$decoded || !isset($decoded->user_id)) {
                ErrorHandler::respondWithError(401, "Unauthorized: Invalid token.");
            }

            $user = $this->userRepository->getUserById($decoded->user_id);

            if (!$user) {
                ErrorHandler::respondWithError(401, "Unauthorized: User not found.");
            }

            // Set user details to session (optional, can be managed elsewhere if preferred)
            $_SESSION['user'] = [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "role" => $user->getRole()
            ];

            return $user;
        } catch (\Throwable $e) {
            ErrorHandler::handleException($e);
        }
    }

    // Ensure that the user has an admin role
    public function requireAdmin() {
        // Check if the user is logged in and has an admin role
        if (empty($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
            ErrorHandler::respondWithError(403, "Forbidden: Admin access required.");
        }
    }
}