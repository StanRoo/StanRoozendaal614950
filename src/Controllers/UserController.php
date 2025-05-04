<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\UserService;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

class UserController {
    private $userService;
    private $authMiddleware;

    public function __construct(UserService $userService, AuthMiddleware $authMiddleware) {
        $this->userService = $userService;
        $this->authMiddleware = $authMiddleware;
    }

    public function getAllUsers(): void
    {
        $decodedUser = $this->authMiddleware->verifyToken();

        if ($decodedUser->role !== 'admin') {
            ResponseHelper::error('Access denied. Admins only.', 403);
            return;
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

        $filters = [
            'id' => $_GET['id'] ?? null,
            'username' => $_GET['username'] ?? null,
            'email' => $_GET['email'] ?? null,
            'status' => $_GET['status'] ?? null,
            'role' => $_GET['role'] ?? null,
            'last_login' => $_GET['last_login'] ?? null,
        ];

        $usersResult = $this->userService->getAllUsers($decodedUser, $page, $limit, $filters);

        if (!$usersResult['success']) {
            ResponseHelper::error($usersResult['message'], 500);
            return;
        }

        ResponseHelper::success([
            'users' => $usersResult['data'],
            'pagination' => $usersResult['pagination']
        ], 'Users fetched successfully.');
    }

    public function getUserById($userId): void {
        $userResult = $this->userService->getUserById($userId);
        $user = $userResult['data'];
        if (!$user) {
            ResponseHelper::error('User not found.', 404);
        }

        ResponseHelper::success([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'profile_picture_url' => $user->getProfilePictureUrl(),
                'bio' => $user->getBio(),
                'status' => $user->getStatus(),
                'created_at' => $user->getCreatedAt(),
                'updated_at' => $user->getUpdatedAt(),
                'balance' => $user->getBalance(),
            ]
        ], 'User fetched successfully.');
    }

    public function getUserBalance(): void {
        $decodedUser = $this->authMiddleware->verifyToken();

        $balanceResult = $this->userService->getBalance($decodedUser->id);
        $balance = $balanceResult['data'];
        ResponseHelper::success(['balance' => $balance], 'Balance fetched successfully.');
    }

    public function updateUser($userId): void {
        $decodedUser = $this->authMiddleware->verifyToken();
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            ResponseHelper::success(['error' => "Invalid fields."]);
        }

        $result = $this->userService->updateUser($userId, $data, $decodedUser);

        if ($result === null) {
            ResponseHelper::error('Unauthorized or forbidden', 403);
        }

        ResponseHelper::success(null, 'User updated successfully.');
    }

    public function updateProfilePicture($userId): void {
        $decodedUser = $this->authMiddleware->verifyToken();
        $data = json_decode(file_get_contents("php://input"), true);

        if ($decodedUser->id !== $userId) {
            ResponseHelper::error('Unauthorized', 403);
        }

        $file = $_FILES['profile_picture'] ?? null;
        $result = $this->userService->updateProfilePicture($userId, $data, $file);

        if ($result) {
            ResponseHelper::success(null, 'Profile picture updated successfully');
        } else {
            ResponseHelper::error('Failed to update profile picture', 500);
        }
    }

    public function createAccount(): void {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !is_array($data)) {
            ResponseHelper::error('Invalid fields.', 400);
        }

        $usernameValidation = Validator::validateUsername($data['username']);
        if ($usernameValidation !== true) {
            ResponseHelper::error($usernameValidation, 400);
        }

        $emailValidation = Validator::validateEmail($data['email']);
        if ($emailValidation !== true) {
            ResponseHelper::error($emailValidation, 400);
        }

        $passwordValidation = Validator::validatePassword($data['password']);
        if ($passwordValidation !== true) {
            ResponseHelper::error($passwordValidation, 400);
        }

        if ($data['password'] !== $data['confirmPassword']) {
            ResponseHelper::error('Passwords do not match.', 400);
        }

        $result = $this->userService->createAccount($data);

        if ($result) {
            ResponseHelper::success(null, 'Account created successfully!');
        } else {
            ResponseHelper::error('Failed to create account. Please try again.', 500);
        }
    }

    public function claimDailyCuboCoins(): void {
        $decodedUser = $this->authMiddleware->verifyToken();
        $result = $this->userService->claimDailyReward($decodedUser->id);
    
        if ($result['success']) {
            $balance = $result['data']['balance'];
            $claimed_today = $result['data']['claimed_today'];
            ResponseHelper::success([
                'message' => $result['message'],
                'balance' => $balance,
                'claimed_today' => $claimed_today
            ], 'Daily reward claimed successfully.');
        } else {
            ResponseHelper::error($result['message'], 400);
        }
    }

    public function deleteUser($id): void {
        $decodedUser = $this->authMiddleware->verifyToken();

        $result = $this->userService->deleteUser($id, $decodedUser);

        if ($result === null) {
            ResponseHelper::error('Access denied. Admins only.', 403);
        }

        ResponseHelper::success(null, 'User deleted successfully!');
    }
}