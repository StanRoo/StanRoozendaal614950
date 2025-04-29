<?php

namespace App\Repositories;

use App\Models\UserModel;
use PDO;
use PDOException;

class UserRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers(): array {
        $stmt = $this->pdo->prepare("SELECT id, username, email, password, role, profile_picture_url, status, bio, last_login, created_at, updated_at, last_login, balance, last_daily_claim FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        return array_map(fn($user) => new UserModel($user), $users);
    }

    public function getUserById($userId): ?UserModel {
        $stmt = $this->pdo->prepare("SELECT id, username, email, password, role, status, profile_picture_url, bio, created_at, updated_at, last_login, balance, last_daily_claim FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userData ? new UserModel($userData) : null;
    }

    public function getUserByUsername(string $username): ?UserModel {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, profile_picture_url, bio, status, role, last_login, created_at, updated_at, balance
            FROM users WHERE username = ?
        ");
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);       
        return $userData ? new UserModel($userData) : null;
    }

    public function getUserByEmail($email): ?UserModel {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, profile_picture_url, bio, status, role, last_login, created_at, updated_at, balance
            FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userData ? new UserModel($userData) : null;
    }

    public function createUser($username, $email, $password, $bio, $profilePictureUrl, $balance): ?UserModel {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, bio, profile_picture_url, balance) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$username, $email, $password, $bio, $profilePictureUrl, $balance])) {
            return $this->getUserById($this->pdo->lastInsertId());
        }
        return null;
    }

    public function updateUser($userId, $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        if (empty($fields)) {
            return false;
        }
        $sql = "UPDATE users SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $params['id'] = $userId;
        return $stmt->execute($params);
    }

    public function updateProfilePicture($userId, $profilePictureUrl): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture_url = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$profilePictureUrl, $userId]);
    }

    public function deleteUser($userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateBalance($userId, $newBalance): bool {
        $sql = "UPDATE users SET balance = :balance, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':balance', $newBalance);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function getLastClaimedTimestamp($userId): ?string {
        $stmt = $this->pdo->prepare("SELECT last_daily_claim FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['last_daily_claim'] : null;
    }

    public function updateBalanceAndClaimTime($userId, $balance, $claimTime): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET balance = ?, last_daily_claim = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$balance, $claimTime, $userId]);
    }

    public function updateLastLogin($userId): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updatePasswordByEmail($email, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
    }
    
    public function storeResetToken($email, $token, $expiresAt) {
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);
    }
    
    public function getResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }  
    
    public function deleteResetToken($token) {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }
}