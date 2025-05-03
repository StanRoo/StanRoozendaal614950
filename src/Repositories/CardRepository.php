<?php

namespace App\Repositories;

use App\Models\CardModel;
use PDO;

class CardRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getCardById($id): ?CardModel {
        $stmt = $this->pdo->prepare("SELECT * FROM cards WHERE id = ?");
        $stmt->execute([$id]);
        $cardData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $cardData ? new CardModel($cardData) : null;
    }

    public function getUserCards(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        $sql = "SELECT * FROM cards WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name_asc':
                    $sql .= ' ORDER BY name ASC';
                    break;
                case 'created_asc':
                    $sql .= ' ORDER BY created_at ASC';
                    break;
                case 'created_desc':
                default:
                    $sql .= ' ORDER BY created_at DESC';
                    break;
            }
        } else {
            $sql .= ' ORDER BY created_at DESC';
        }
        $sql .= ' LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $cards = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($card) => new \App\Models\CardModel($card), $cards);
    }

    public function getUserCardsCount(int $userId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM cards WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create($cardData) {
        $query = "INSERT INTO cards 
            (user_id, owner_id, name, type, hp, attack, defense, speed, image_url, rarity, is_listed, created_at, updated_at)
            VALUES 
            (:user_id, :owner_id, :name, :type, :hp, :attack, :defense, :speed, :image_url, :rarity, :is_listed, :created_at, :updated_at)";
        
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute($cardData)) {
            $cardData['id'] = $this->pdo->lastInsertId();
            return new CardModel($cardData);
        }
        return false;
    }

    public function delete($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cards WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateCardOwner($cardId, $newOwnerId): bool {
        $sql = "UPDATE cards SET user_id = :newOwnerId, updated_at = NOW() WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':newOwnerId', $newOwnerId);
        $stmt->bindParam(':cardId', $cardId);
        return $stmt->execute();
    }

    public function setCardListedStatus(int $cardId, bool $isListed): bool {
        $stmt = $this->pdo->prepare("
            UPDATE cards
            SET is_listed = :is_listed, updated_at = NOW()
            WHERE id = :card_id
            ");
        return $stmt->execute([
            'is_listed' => $isListed ? 1 : 0,
            'card_id' => $cardId
        ]);
    }

    public function getAllCards(int $limit, int $offset, array $filters = []): array
    {
        $sql = "
            SELECT c.*, u.username AS owner_username
            FROM cards c
            JOIN users u ON c.owner_id = u.id
        ";
        $where = [];
        $params = [];
        if (!empty($filters['owner_id'])) {
            $where[] = 'c.owner_id = :owner_id';
            $params[':owner_id'] = $filters['owner_id'];
        }
        if (!empty($filters['name'])) {
            $where[] = 'c.name LIKE :name';
            $params[':name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'c.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (isset($filters['is_listed']) && $filters['is_listed'] !== '') {
            $where[] = 'c.is_listed = :is_listed';
            $params[':is_listed'] = (int)$filters['is_listed'];
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if (!empty($filters['created_at'])) {
            $direction = strtoupper($filters['created_at']) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY c.created_at $direction";
        } else {
            $sql .= " ORDER BY c.created_at DESC";
        }
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $cardsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new \App\Models\CardModel($data), $cardsData);
    }

    public function getCardsCount(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM cards c
            JOIN users u ON c.owner_id = u.id
        ";
        $where = [];
        $params = [];
        if (!empty($filters['owner_id'])) {
            $where[] = 'c.owner_id = :owner_id';
            $params[':owner_id'] = $filters['owner_id'];
        }
        if (!empty($filters['name'])) {
            $where[] = 'c.name LIKE :name';
            $params[':name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'c.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (isset($filters['is_listed']) && $filters['is_listed'] !== '') {
            $where[] = 'c.is_listed = :is_listed';
            $params[':is_listed'] = (int)$filters['is_listed'];
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
    
    public function updateCard(int $id, array $data): void {
        $stmt = $this->pdo->prepare("
            UPDATE cards 
            SET owner_id = :owner_id, is_listed = :is_listed, updated_at = NOW()
            WHERE id = :id
        ");
    
        $stmt->execute([
            ':id' => $id,
            ':owner_id' => $data['owner_id'],
            ':is_listed' => $data['is_listed']
        ]);
    }
}