<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use App\Config;
use App\Utils\Validator;
use App\Services\MailService;

class AuthService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function login($username, $password, $rememberMe = false): array {
        $user = $this->userRepository->getUserByUsername($username);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }
        if ($user->getStatus() === 'banned') {
            return ['success' => false, 'message' => 'Your account has been banned.'];
        }
        if ($user->getStatus() === 'inactive') {
            return ['success' => false, 'message' => 'Your account is not active.'];
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

        $expirationTime = $rememberMe ? (60 * 60 * 24 * 30) : (60 * 60 * 2); // 30 days vs 2 hours

        $payload = [
            'user_id' => $user->getId(),
            'user' => $userData,
            'iat' => time(),
            'exp' => time() + $expirationTime
        ];

        $jwt = JWT::encode($payload, Config::JWT_SECRET, 'HS256');

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $jwt,
                'user' => $userData
            ]
        ];
    }

    public function register($data): array {
        $usernameValidation = Validator::validateUsername($data['username']);
        if ($usernameValidation !== true) {
            return ['success' => false, 'message' => $usernameValidation];
        }
    
        $emailValidation = Validator::validateEmail($data['email']);
        if ($emailValidation !== true) {
            return ['success' => false, 'message' => $emailValidation];
        }
    
        $passwordValidation = Validator::validatePassword($data['password']);
        if ($passwordValidation !== true) {
            return ['success' => false, 'message' => $passwordValidation];
        }
    
        if ($this->userRepository->getUserByEmail($data['email'])) {
            return ['success' => false, 'message' => ['Email is already registered.']];
        }
    
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $newUser = $this->userRepository->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            "I love Pokémon :)",
            "/images/profile.png"
        );
    
        if (!$newUser) {
            return ['success' => false, 'message' => ['Failed to create account.']];
        }
    
        return ['success' => true, 'message' => 'Account created successfully!'];
    }    

    public function sendResetLink($email): array {
        $user = $this->userRepository->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
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

        $mailSent = MailService::send($email, $user->getUsername(), $subject, $body);

        return $mailSent
            ? ['success' => true, 'message' => 'Reset link sent successfully.']
            : ['success' => false, 'message' => 'Failed to send reset link.'];
    }

    public function resetPassword($token, $newPassword): array {
        $reset = $this->userRepository->getResetToken($token);

        if (!$reset || strtotime($reset['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Invalid or expired token.'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userRepository->updatePasswordByEmail($reset['email'], $hashedPassword);
        $this->userRepository->deleteResetToken($token);

        return ['success' => true, 'message' => 'Password reset successfully.'];
    }
}