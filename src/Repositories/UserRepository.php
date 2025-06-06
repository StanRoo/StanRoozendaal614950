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

    // --------- GET ----------
    public function getAllUsers(int $limit, int $offset, array $filters = []): array
    {
        $sql = "
            SELECT id, username, email, password, role, profile_picture_url, status, bio, last_login, created_at, updated_at, balance, last_daily_claim
            FROM users
        ";

        $where = [];
        $params = [];

        if (!empty($filters['id'])) {
            $where[] = 'id = :id';
            $params[':id'] = $filters['id'];
        }

        if (!empty($filters['username'])) {
            $where[] = 'username LIKE :username';
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        if (!empty($filters['email'])) {
            $where[] = 'email LIKE :email';
            $params[':email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['role'])) {
            $where[] = 'role = :role';
            $params[':role'] = $filters['role'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($filters['last_login'])) {
            $direction = strtoupper($filters['last_login']) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY last_login $direction";
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($user) => new \App\Models\UserModel($user), $users);
    }
    
    public function getUsersCount(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM users";
        $where = [];
        $params = [];

        if (!empty($filters['id'])) {
            $where[] = 'id = :id';
            $params[':id'] = $filters['id'];
        }

        if (!empty($filters['username'])) {
            $where[] = 'username LIKE :username';
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        if (!empty($filters['email'])) {
            $where[] = 'email LIKE :email';
            $params[':email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['role'])) {
            $where[] = 'role = :role';
            $params[':role'] = $filters['role'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
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

    public function getLastClaimedTimestamp($userId): ?string {
        $stmt = $this->pdo->prepare("SELECT last_daily_claim FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['last_daily_claim'] : null;
    }

    public function getResetToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }  

    // -------- UPDATE --------
    public function updateUser($userId, $data): bool {
        $allowedFields = ['username', 'email', 'bio', 'profile_picture_url', 'status'];
        $fields = [];
        $params = []; 
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
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

    public function updateBalance($userId, $newBalance): bool {
        $sql = "UPDATE users SET balance = :balance, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':balance', $newBalance);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
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
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
    }

    // -------- POST ----------
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
    
    public function storeResetToken($email, $token, $expiresAt) {
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);
    }

    // -------- DELETE --------
    public function deleteUser($userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function deleteResetToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }
}