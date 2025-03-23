<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Utils\Validator;
use App\Utils\ErrorHandler;

class UserService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function getUserById($userId) {
        return $this->userRepository->getUserById($userId) ?? null;
    }

    public function getAllUsers($decodedUser) {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return ErrorHandler::respondWithError(403, "Unauthorized: Admin access required.");
        }
        return $this->userRepository->getAllUsers();
    }

    public function updateUser($userId, $data, $decodedUser) {
        if ($decodedUser->role === 'admin') {
            if (isset($data['role']) || isset($data['status'])) {
                return $this->userRepository->updateUser($userId, $data);
            }
        } elseif ($decodedUser->id == $userId) {
            unset($data['role'], $data['status']);
            return $this->userRepository->updateUser($userId, $data);
        }

        return null;
    }

    public function deleteUser($userId, $decodedUser) {
        if ($decodedUser->role !== 'admin') {
            return ErrorHandler::respondWithError(403, "Unauthorized: Admin access required.");
        }
        return $this->userRepository->deleteUser($userId);
    }

    public function updateProfilePicture($userId, $data, $file = null) {
        if (!empty($data['profile_picture_url'])) {
            return $this->userRepository->updateProfilePicture($userId, $data['profile_picture_url']);
        }

        if ($file) {
            $targetDir = "uploads/";
            $fileName = uniqid() . "_" . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png"];

            if (!in_array($fileType, $allowedTypes)) {
                return null;
            }

            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return $this->userRepository->updateProfilePicture($userId, $targetFilePath);
            }
        }
        return null;
    }

    public function createAccount($data) {
        $usernameValidation = Validator::validateUsername($data['username']);
        $emailValidation = Validator::validateEmail($data['email']);
        $passwordValidation = Validator::validatePassword($data['password']);

        if ($usernameValidation !== true) return ErrorHandler::respondWithError(400, $usernameValidation);
        if ($emailValidation !== true) return ErrorHandler::respondWithError(400, $emailValidation);
        if ($passwordValidation !== true) return ErrorHandler::respondWithError(400, $passwordValidation);

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $defaultBio = "I love Pokémon!";
        $defaultProfilePictureUrl = "/images/profile.png";

        return $this->userRepository->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            $defaultBio,
            $defaultProfilePictureUrl
        );
    }
}