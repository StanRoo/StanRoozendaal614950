<?php

namespace App\Repositories;

use App\Config;
use App\Models\UserModel;
use PDO;
use PDOException;

class UserRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getPDO();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT id, username, email, role, profile_picture_url, status, bio, last_login, created_at, updated_at FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($userId) {
        $stmt = $this->pdo->prepare("SELECT id, username, email, profile_picture_url, status, bio FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByUsername(string $username): ?UserModel {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, profile_picture_url, bio, status, role, last_login, created_at, updated_at
            FROM users WHERE username = ?
        ");
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? new UserModel($userData) : null;
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $email, $password, $bio) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, bio) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $password, $bio]);
    }

    public function updateUser(int $userId, array $userData): bool {
        if (!$this->pdo) {
            error_log("Database connection is missing in UserRepository.");
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($userData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        if (empty($fields)) {
            error_log("No valid fields to update for user ID: $userId");
            return false;
        }

        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($values);

            if ($success) {
                error_log("User ID $userId updated successfully.");
                return true;
            } else {
                error_log("Database update failed for user ID: $userId");
                return false;
            }
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfilePicture($userId, $profilePictureUrl): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture_url = ? WHERE id = ?");
        return $stmt->execute([$profilePictureUrl, $userId]);
    }
}