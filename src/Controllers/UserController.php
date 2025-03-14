<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\UserService;
use App\Utils\Validator;

class UserController {
    private $userService;
    private $authMiddleware;

    public function __construct() {
        $this->userService = new UserService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function getUser($userId) {
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            Response::error(404, "User not found.");
            exit();
        }

        echo json_encode([
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "profile_picture_url" => $user->getProfilePictureUrl(),
                "bio" => $user->getBio(),
                "status" => $user->getStatus(),
                "created_at" => $user->getCreatedAt(),
                "updated_at" => $user->getUpdatedAt(),
            ]
        ]);
    }

    public function getAllUsers() {
        $decodedUser = $this->authMiddleware->verifyToken();

        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
            return;
        }

        $users = $this->userService->getAllUsers($decodedUser);
        echo json_encode(["users" => $users]);
    }

    public function updateUser($userId) {
        $decodedUser = $this->authMiddleware->verifyToken();

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
            return;
        }

        $result = $this->userService->updateUser($userId, $data, $decodedUser);

        if ($result === null) {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized or forbidden"]);
            return;
        }

        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    }

    public function deleteUser($id) {
        $decodedUser = $this->authMiddleware->verifyToken();

        $result = $this->userService->deleteUser($id, $decodedUser);

        if ($result === null) {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
            return;
        }

        echo json_encode(["message" => "User deleted successfully!"]);
    }

    public function createAccount() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
            return;
        }

        $usernameValidation = Validator::validateUsername($data['username']);
        if ($usernameValidation !== true) {
            echo json_encode(["error" => $usernameValidation]);
            return;
        }

        $emailValidation = Validator::validateEmail($data['email']);
        if ($emailValidation !== true) {
            echo json_encode(["error" => $emailValidation]);
            return;
        }

        $passwordValidation = Validator::validatePassword($data['password']);
        if ($passwordValidation !== true) {
            echo json_encode(["error" => $passwordValidation]);
            return;
        }

        if ($data['password'] !== $data['confirmPassword']) {
            echo json_encode(["error" => "Passwords do not match!"]);
            return;
        }

        $result = $this->userService->createAccount($data);

        if ($result) {
            echo json_encode(["message" => "Account created successfully!"]);
        } else {
            echo json_encode(["error" => "Failed to create account. Please try again."]);
        }
    }

    public function updateProfilePicture($userId) {
        $decodedUser = $this->authMiddleware->verifyToken();
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        if ($decodedUser->id !== $userId) {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
            return;
        }
    
        $file = $_FILES['profile_picture'] ?? null;
        $result = $this->userService->updateProfilePicture($userId, $data, $file);
    
        if ($result) {
            echo json_encode(["success" => true, "message" => "Profile picture updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update profile picture"]);
        }
    }
    
}