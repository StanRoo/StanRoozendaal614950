<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\ResponseHelper;
use Firebase\JWT\JWT;
use App\Config;
use App\Utils\Validator;

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
}