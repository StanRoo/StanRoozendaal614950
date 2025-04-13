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
        $defaultBalance = 1000.20;

        return $this->userRepository->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            $defaultBio,
            $defaultProfilePictureUrl,
            $defaultBalance
        );
    }

    public function updateBalance($userId, $newBalance) {
        if ($newBalance < 0) {
            ErrorHandler::respondWithError(400, "Balance cannot be negative.");
        }
        $result = $this->userRepository->updateBalance($userId, $newBalance);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getBalance($userId) {
        $user = $this->userRepository->getUserById($userId);
    
        if (!$user) {
            ErrorHandler::respondWithError(404, "User not found.");
        }
    
        $balance = $user->getBalance();
        $lastClaimed = $user->getLastClaimed();
    
        $claimedToday = false;
        if ($lastClaimed) {
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            $lastClaimedDate = new \DateTime($lastClaimed);
            $lastClaimedDate->setTime(0, 0, 0);
            $claimedToday = $lastClaimedDate->format('Y-m-d') === $today->format('Y-m-d');
        }
    
        return [
            'balance' => $balance,
            'claimed_today' => $claimedToday
        ];
    }
    
    
    public function claimDailyReward($userId) {
        $user = $this->userRepository->getUserById($userId);
    
        if (!$user) {
            return ["success" => false, "message" => "User not found."];
        }
    
        $lastClaimed = $this->userRepository->getLastClaimedTimestamp($userId);
    
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $canClaim = true;

        if ($lastClaimed) {
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            $lastClaimedDate = new \DateTime($lastClaimed);
            $lastClaimedDate->setTime(0, 0, 0);
            $canClaim = $lastClaimedDate->format('Y-m-d') !== $today->format('Y-m-d');
        }
    
        if (!$canClaim) {
            return ["success" => false, "message" => "Daily reward already claimed. Try again later."];
        }
    
        $newBalance = $user->getBalance() + 500;
        $updateResult = $this->userRepository->updateBalanceAndClaimTime($userId, $newBalance, $now->format('Y-m-d'));

        if ($updateResult) {
            return [
                "success" => true,
                "message" => "You've successfully claimed 500 CuboCoins!",
                "balance" => $newBalance,
                "claimed_today" => true,
            ];
        }
    
        return ["success" => false, "message" => "Failed to update balance. Please try again."];
    }
    
    public function addBalance($userId, $amount): bool {
        $user = $this->userRepository->getUserById($userId);
        $newBalance = $user->balance + $amount;
        return $this->userRepository->updateBalance($userId, $newBalance);
    }
}