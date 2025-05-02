<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\Validator;

class UserService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function getUserById($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.', 'data' => null];
        }

        return ['success' => true, 'message' => 'User retrieved successfully.', 'data' => $user];
    }

    public function getAllUsers($decodedUser) {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return ['success' => false, 'message' => 'Unauthorized: Admin access required.', 'data' => null];
        }

        $users = $this->userRepository->getAllUsers();

        return ['success' => true, 'message' => 'Users retrieved successfully.', 'data' => $users];
    }

    public function updateUser($userId, $data, $decodedUser) {
        if ($decodedUser->role === 'admin') {
            if (isset($data['role']) || isset($data['status'])) {
                $this->userRepository->updateUser($userId, $data);
                return ['success' => true, 'message' => "User updated successfully.", 'data' => null];
            }
        } elseif ($decodedUser->id == $userId) {
            unset($data['role'], $data['status']);
            $this->userRepository->updateUser($userId, $data);
            return ['success' => true, 'message' => "Profile updated successfully.", 'data' => null];
        }

        return ['success' => false, 'message' => 'Unauthorized action.', 'data' => null];
    }

    public function deleteUser($userId, $decodedUser) {
        if ($decodedUser->role !== 'admin') {
            return ['success' => false, 'message' => 'Unauthorized: Admin access required.', 'data' => null];
        }

        $this->userRepository->deleteUser($userId);

        return ['success' => true, 'message' => "User deleted successfully.", 'data' => null];
    }

    public function updateProfilePicture($userId, $data, $file = null) {
        if (!empty($data['profile_picture_url'])) {
            $this->userRepository->updateProfilePicture($userId, $data['profile_picture_url']);
            return ['success' => true, 'message' => "Profile picture updated.", 'data' => null];
        }

        if ($file) {
            $targetDir = "uploads/";
            $fileName = uniqid() . "_" . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png"];

            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.', 'data' => null];
            }

            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                $this->userRepository->updateProfilePicture($userId, $targetFilePath);
                return ['success' => true, 'message' => "Profile picture uploaded successfully.", 'data' => null];
            } else {
                return ['success' => false, 'message' => 'Failed to upload profile picture.', 'data' => null];
            }
        }

        return ['success' => false, 'message' => 'No profile picture provided.', 'data' => null];
    }

    public function createAccount($data) {
        $usernameValidation = Validator::validateUsername($data['username']);
        $emailValidation = Validator::validateEmail($data['email']);
        $passwordValidation = Validator::validatePassword($data['password']);

        if ($usernameValidation !== true) {
            return ['success' => false, 'message' => $usernameValidation, 'data' => null];
        }
        if ($emailValidation !== true) {
            return ['success' => false, 'message' => $emailValidation, 'data' => null];
        }
        if ($passwordValidation !== true) {
            return ['success' => false, 'message' => $passwordValidation, 'data' => null];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $defaultBio = "I love Pokémon!";
        $defaultProfilePictureUrl = "/images/profile.png";
        $defaultBalance = 1000.00;

        $this->userRepository->createUser(
            $data['username'],
            $data['email'],
            $hashedPassword,
            $defaultBio,
            $defaultProfilePictureUrl,
            $defaultBalance
        );

        return ['success' => true, 'message' => "Account created successfully.", 'data' => null];
    }

    public function updateBalance($userId, $newBalance) {
        if ($newBalance < 0) {
            return ['success' => false, 'message' => 'Balance cannot be negative.', 'data' => null];
        }

        $result = $this->userRepository->updateBalance($userId, $newBalance);
        if (!$result) {
            return ['success' => false, 'message' => 'Failed to update balance.', 'data' => null];
        }

        return ['success' => true, 'message' => "Balance updated successfully.", 'data' => null];
    }

    public function getBalance($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.', 'data' => null];
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

        return ['success' => true, 'message' => 'Balance retrieved successfully.', 'data' => ['balance' => $balance, 'claimed_today' => $claimedToday]];
    }

    public function claimDailyReward($userId) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.', 'data' => null];
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
                return ['success' => false, 'message' => 'Daily reward already claimed. Please try again tomorrow.', 'data' => null];
            }
        }

        $newBalance = $user->getBalance() + 500;
        $updateResult = $this->userRepository->updateBalanceAndClaimTime($userId, $newBalance, $now->format('Y-m-d'));

        if (!$updateResult) {
            return ['success' => false, 'message' => 'Failed to update balance. Please try again.', 'data' => null];
        }

        return ['success' => true, 'message' => "You've successfully claimed 500 CuboCoins!", 'data' => ["balance" => $newBalance, "claimed_today" => true]];
    }

    public function updateUserBalance($userId, $price) {
        $user = $this->userRepository->getUserById($userId);
        $currentBalance = $user->getBalance();
        $newBalance = $currentBalance - $price;

        if ($newBalance < 0) {
            return ['success' => false, 'message' => 'Balance cannot be negative.', 'data' => null];
        }

        $result = $this->userRepository->updateBalance($userId, $newBalance);
        if (!$result) {
            return ['success' => false, 'message' => 'Failed to update balance.', 'data' => null];
        }

        return ['success' => true, 'message' => "User balance updated successfully.", 'data' => null];
    }

    public function addOwnerBalance($userId, $amount) {
        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.', 'data' => null];
        }

        $newBalance = $user->balance + $amount;
        $result = $this->userRepository->updateBalance($userId, $newBalance);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to add balance.', 'data' => null];
        }

        return ['success' => true, 'message' => "Balance added successfully.", 'data' => null];
    }
}