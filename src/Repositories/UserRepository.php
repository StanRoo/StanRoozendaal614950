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

    public function updateUser($userId, $data) {
        $fields = [];
        $params = [];
    
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
    
        if (empty($fields)) {
            return false;
        }
    
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $params['id'] = $userId;
    
        return $stmt->execute($params);
    }

    public function updateProfilePicture($userId, $profilePictureUrl): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture_url = ? WHERE id = ?");
        return $stmt->execute([$profilePictureUrl, $userId]);
    }

    public function deleteUser($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}