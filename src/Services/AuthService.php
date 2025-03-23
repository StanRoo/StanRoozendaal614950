<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\ErrorHandler;
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
            ErrorHandler::respondWithError(401, 'Invalid credentials');
        }

        $userData = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "profile_picture_url" => $user->getProfilePictureUrl(),
            'bio' => $user->getBio(),
            "created_at" => $user->getCreatedAt(),
            "updated_at" => $user->getUpdatedAt()
        ];

        $payload = [
            'user_id' => $user->getId(),
            'user' => $userData,
            "iat" => time(),
            "exp" => time() + (60 * 60)  // Expire in 1 hour
        ];

        $jwt = JWT::encode($payload, Config::JWT_SECRET, 'HS256');

        return [
            'message' => 'Login successful',
            'token' => $jwt,
            'user' => $userData
        ];
    }

    public function register($data) {
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

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        if ($this->userRepository->getUserByEmail($data['email'])) {
            ErrorHandler::respondWithError(400, 'Email is already registered.');
        }

        $newUser = $this->userRepository->createUser($data['username'], $data['email'], $hashedPassword, "I love Pokémon :)", "/images/profile.png");

        if (!$newUser) {
            ErrorHandler::respondWithError(500, 'Failed to create account.');
        }

        return ['error' => false, 'message' => 'Account created successfully!'];
    }
}