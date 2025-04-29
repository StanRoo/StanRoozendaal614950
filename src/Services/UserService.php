<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

class UserService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function getUserById($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            ResponseHelper::error('User not found.', 404);
        }
        ResponseHelper::success(['user' => $user->toArray()]);
    }

    public function getAllUsers($decodedUser) {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            ResponseHelper::error('Unauthorized: Admin access required.', 403);
        }

        $users = $this->userRepository->getAllUsers();
        ResponseHelper::success($users);
    }

    public function updateUser($userId, $data, $decodedUser) {
        if ($decodedUser->role === 'admin') {
            if (isset($data['role']) || isset($data['status'])) {
                $this->userRepository->updateUser($userId, $data);
                ResponseHelper::success(null, "User updated successfully.");
            }
        } elseif ($decodedUser->id == $userId) {
            unset($data['role'], $data['status']);
            $this->userRepository->updateUser($userId, $data);
            ResponseHelper::success(null, "Profile updated successfully.");
        }

        ResponseHelper::error('Unauthorized action.', 403);
    }

    public function deleteUser($userId, $decodedUser) {
        if ($decodedUser->role !== 'admin') {
            ResponseHelper::error('Unauthorized: Admin access required.', 403);
        }

        $this->userRepository->deleteUser($userId);
        ResponseHelper::success(null, "User deleted successfully.");
    }

    public function updateProfilePicture($userId, $data, $file = null) {
        if (!empty($data['profile_picture_url'])) {
            $this->userRepository->updateProfilePicture($userId, $data['profile_picture_url']);
            ResponseHelper::success(null, "Profile picture updated.");
        }

        if ($file) {
            $targetDir = "uploads/";
            $fileName = uniqid() . "_" . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png"];

            if (!in_array($fileType, $allowedTypes)) {
                ResponseHelper::error('Invalid file type. Only JPG, JPEG, and PNG are allowed.', 400);
            }

            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                $this->userRepository->updateProfilePicture($userId, $targetFilePath);
                ResponseHelper::success(null, "Profile picture uploaded successfully.");
            } else {
                ResponseHelper::error('Failed to upload profile picture.', 500);
            }
        }
        ResponseHelper::error('No profile picture provided.', 400);
    }

    public function createAccount($data) {
        $usernameValidation = Validator::validateUsername($data['username']);
        $emailValidation = Validator::validateEmail($data['email']);
        $passwordValidation = Validator::validatePassword($data['password']);

        if ($usernameValidation !== true) ResponseHelper::error($usernameValidation, 400);
        if ($emailValidation !== true) ResponseHelper::error($emailValidation, 400);
        if ($passwordValidation !== true) ResponseHelper::error($passwordValidation, 400);

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $defaultBio = "I love Pokémon!";
        $defaultProfilePictureUrl = "/images/profile.png";
        $defaultBalance = 1000.20;

        $this->userRepository->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            $defaultBio,
            $defaultProfilePictureUrl,
            $defaultBalance
        );

        ResponseHelper::success(null, "Account created successfully.");
    }

    public function updateBalance($userId, $newBalance) {
        if ($newBalance < 0) {
            ResponseHelper::error('Balance cannot be negative.', 400);
        }

        $result = $this->userRepository->updateBalance($userId, $newBalance);
        if (!$result) {
            ResponseHelper::error('Failed to update balance.', 500);
        }

        ResponseHelper::success(null, "Balance updated successfully.");
    }

    public function getBalance($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            ResponseHelper::error('User not found.', 404);
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

        ResponseHelper::success([
            'balance' => $balance,
            'claimed_today' => $claimedToday
        ]);
    }

    public function claimDailyReward($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            ResponseHelper::error('User not found.', 404);
        }

        $lastClaimed = $this->userRepository->getLastClaimedTimestamp($userId);
        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        if ($lastClaimed) {
            $today = new \DateTime();
            $today->setTime(0, 0, 0);
            $lastClaimedDate = new \DateTime($lastClaimed);
            $lastClaimedDate->setTime(0, 0, 0);

            if ($lastClaimedDate->format('Y-m-d') === $today->format('Y-m-d')) {
                ResponseHelper::error('Daily reward already claimed. Please try again tomorrow.', 400);
            }
        }

        $newBalance = $user->getBalance() + 500;
        $updateResult = $this->userRepository->updateBalanceAndClaimTime($userId, $newBalance, $now->format('Y-m-d'));

        if (!$updateResult) {
            ResponseHelper::error('Failed to update balance. Please try again.', 500);
        }

        ResponseHelper::success([
            "balance" => $newBalance,
            "claimed_today" => true,
            "message" => "You've successfully claimed 500 CuboCoins!"
        ]);
    }

    public function updateUserBalance($userId, $price) {
        $user = $this->userRepository->getUserById($userId);
        $currentBalance = $user->getBalance();
        $newBalance = $currentBalance - $price;

        if ($newBalance < 0) {
            ResponseHelper::error('Balance cannot be negative.', 400);
        }

        $result = $this->userRepository->updateBalance($userId, $newBalance);
        if (!$result) {
            ResponseHelper::error('Failed to update balance.', 500);
        }

        ResponseHelper::success(null, "User balance updated successfully.");
    }

    public function addOwnerBalance($userId, $amount) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            ResponseHelper::error('User not found.', 404);
        }

        $newBalance = $user->balance + $amount;
        $result = $this->userRepository->updateBalance($userId, $newBalance);

        if (!$result) {
            ResponseHelper::error('Failed to add balance.', 500);
        }

        ResponseHelper::success(null, "Balance added successfully.");
    }
}