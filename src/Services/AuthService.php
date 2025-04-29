<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\ResponseHelper;
use Firebase\JWT\JWT;
use App\Config;
use App\Utils\Validator;
use App\Services\MailService;

class AuthService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function login($username, $password) {
        $user = $this->userRepository->getUserByUsername($username);

        if (!$user || !password_verify($password, $user->getPassword())) {
            ResponseHelper::error('Invalid credentials.', 400);
        }

        if ($user->getStatus() === 'banned') {
            ResponseHelper::error('Your account has been banned.', 403);
        }
    
        if ($user->getStatus() === 'inactive') {
            ResponseHelper::error('Your account is not active.', 403);
        }

        $this->userRepository->updateLastLogin($user->getId());

        $userData = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "profile_picture_url" => $user->getProfilePictureUrl(),
            'bio' => $user->getBio(),
            "created_at" => $user->getCreatedAt(),
            "updated_at" => $user->getUpdatedAt(),
            "balance" => $user->getBalance(),
            "last_login" => $user->getLastLogin(),
        ];

        $payload = [
            'user_id' => $user->getId(),
            'user' => $userData,
            "iat" => time(),
            "exp" => time() + (60 * 60)  // Expire in 1 hour
        ];

        $jwt = JWT::encode($payload, Config::JWT_SECRET, 'HS256');

        ResponseHelper::success([
            'message' => 'Login successful',
            'token' => $jwt,
            'user' => $userData
        ]);
    }

    public function register($data) {
        $usernameValidation = Validator::validateUsername($data['username']);
        if ($usernameValidation !== true) {
            ResponseHelper::error('Invalid username.', 400);
        }

        $emailValidation = Validator::validateEmail($data['email']);
        if ($emailValidation !== true) {
            ResponseHelper::error('Invalid Email.', 400);
        }

        $passwordValidation = Validator::validatePassword($data['password']);
        if ($passwordValidation !== true) {
            ResponseHelper::error('Invalid password.', 400);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        if ($this->userRepository->getUserByEmail($data['email'])) {
            ResponseHelper::error('Email is already registered.', 400);
        }

        $newUser = $this->userRepository->createUser($data['username'], $data['email'], $hashedPassword, "I love Pokémon :)", "/images/profile.png");

        if (!$newUser) {
            ResponseHelper::error('Failed to create account.', 500);
        }

        ResponseHelper::success(null, 'Account created successfully!');
    }

    public function sendResetLink($email) {
        $user = $this->userRepository->getUserByEmail($email);
        if (!$user) {
            return ['error' => true, 'message' => 'User not found.'];
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userRepository->storeResetToken($email, $token, $expiresAt);

        $resetLink = "http://localhost:8000/reset-password?token=$token";
        $subject = 'Reset your password';
        $body = "Hi {$user->getUsername()},<br><br>"
            . "Click the link below to reset your password:<br>"
            . "<a href='{$resetLink}'>{$resetLink}</a><br><br>"
            . "If you didn't request this, you can ignore this email.";

        return MailService::send($email, $user->getUsername(), $subject, $body);
    } 
    
    public function resetPassword($token, $newPassword) {
        $reset = $this->userRepository->getResetToken($token);
    
        if (!$reset || strtotime($reset['expires_at']) < time()) {
            return ['error' => true, 'message' => 'Invalid or expired token.'];
        }
    
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userRepository->updatePasswordByEmail($reset['email'], $hashedPassword);
        $this->userRepository->deleteResetToken($token);
    
        return ['success' => true];
    }
}