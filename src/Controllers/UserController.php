<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\Validator;

class UserController {
    private $userService;
    private $authMiddleware;

    public function __construct(UserService $userService, AuthMiddleware $authMiddleware) {
        $this->userService = $userService;
        $this->authMiddleware = $authMiddleware;
    }

    public function getUser($userId) {
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            ErrorHandler::respondWithError(404, "User not found.");
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
                "balance" => $user->getBalance(),
            ]
        ]);
    }

    public function getAllUsers() {
        $decodedUser = $this->authMiddleware->verifyToken();

        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            ErrorHandler::respondWithError(403, "Access denied. Admins only.");
        }

        $users = $this->userService->getAllUsers($decodedUser);
        echo json_encode(["users" => $users]);
    }

    public function updateUser($userId) {
        $decodedUser = $this->authMiddleware->verifyToken();

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            ErrorHandler::respondWithError(400, "Invalid request data");
        }

        $result = $this->userService->updateUser($userId, $data, $decodedUser);

        if ($result === null) {
            ErrorHandler::respondWithError(403, "Unauthorized or forbidden");
        }

        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    }

    public function deleteUser($id) {
        $decodedUser = $this->authMiddleware->verifyToken();

        $result = $this->userService->deleteUser($id, $decodedUser);

        if ($result === null) {
            ErrorHandler::respondWithError(403, "Access denied. Admins only.");
        }

        echo json_encode(["message" => "User deleted successfully!"]);
    }

    public function createAccount() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            ErrorHandler::respondWithError(400, "Invalid request data");
        }

        $usernameValidation = Validator::validateUsername($data['username']);
        if ($usernameValidation !== true) {
            ErrorHandler::respondWithError(400, $usernameValidation);
        }

        $emailValidation = Validator::validateEmail($data['email']);
        if ($emailValidation !== true) {
            ErrorHandler::respondWithError(400, $emailValidation);
        }

        $passwordValidation = Validator::validatePassword($data['password']);
        if ($passwordValidation !== true) {
            ErrorHandler::respondWithError(400, $passwordValidation);
        }

        if ($data['password'] !== $data['confirmPassword']) {
            ErrorHandler::respondWithError(400, "Passwords do not match!");
        }

        $result = $this->userService->createAccount($data);

        if ($result) {
            echo json_encode(["message" => "Account created successfully!"]);
        } else {
            ErrorHandler::respondWithError(500, "Failed to create account. Please try again.");
        }
    }

    public function updateProfilePicture($userId) {
        $decodedUser = $this->authMiddleware->verifyToken();
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        if ($decodedUser->id !== $userId) {
            ErrorHandler::respondWithError(403, "Unauthorized");
        }
    
        $file = $_FILES['profile_picture'] ?? null;
        $result = $this->userService->updateProfilePicture($userId, $data, $file);
    
        if ($result) {
            echo json_encode(["success" => true, "message" => "Profile picture updated successfully"]);
        } else {
            ErrorHandler::respondWithError(500, "Failed to update profile picture");
        }
    }

    public function getUserBalance() {
        $decodedUser = $this->authMiddleware->verifyToken();
    
        $balance = $this->userService->getBalance($decodedUser->id);
    
        echo json_encode($balance);
    }
    
    public function claimDailyCuboCoins() {
        $decodedUser = $this->authMiddleware->verifyToken();
    
        $result = $this->userService->claimDailyReward($decodedUser->id);
    
        if ($result['success']) {
            echo json_encode([
                "success" => true,
                "message" => $result['message'],
                "balance" => $result['balance']
            ]);
        } else {
            ErrorHandler::respondWithError(400, $result['message']);
        }
    }
}