<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\ResponseHelper;

class AuthController {
    private $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function login() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (empty($data['username']) || empty($data['password'])) {
                ResponseHelper::error('Username and Password are required.', 400);
                return;
            }

            $username = $data['username'];
            $password = $data['password'];
            $rememberMe = isset($data['remember']) ? (bool)$data['remember'] : false;
    
            $result = $this->authService->login($username, $password, $rememberMe);

            if (!$result['success']) {
                ResponseHelper::error($result['message'], 400);
            } else {
                ResponseHelper::success([
                    'token' => $result['data']['token'],
                    'user' => $result['data']['user']
                ], "Login successful.");
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred during login: " . $e->getMessage(), 500);
        }
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['username'], $data['email'], $data['password'])) {
                ResponseHelper::error('Username, Email and Password are required.', 400);
                return;
            }

            $result = $this->authService->register($data);

            if (isset($result['error'])) {
                ResponseHelper::error($result['message'], 400);
            } else {
                ResponseHelper::success(null, "Account created successfully!");
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred during registration: " . $e->getMessage(), 500);
        }
    }

    public function forgotPassword() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data['email'])) {
                ResponseHelper::error('Email is required.', 400);
                return;
            }
    
            $result = $this->authService->sendResetLink($data['email']);
    
            if (isset($result['error'])) {
                ResponseHelper::error($result['message'], 404);
            } else {
                ResponseHelper::success(null, 'Reset link sent if email exists.');
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("Error: " . $e->getMessage(), 500);
        }
    }
    
    public function resetPassword() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data['token']) || empty($data['newPassword'])) {
                ResponseHelper::error('Token and new password are required.', 400);
                return;
            }
    
            $result = $this->authService->resetPassword($data['token'], $data['newPassword']);
    
            if (isset($result['error'])) {
                ResponseHelper::error($result['message'], 400);
            } else {
                ResponseHelper::success(null, 'Password reset successful.');
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("Error: " . $e->getMessage(), 500);
        }
    }
}