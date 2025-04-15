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

    public function getUserCards($userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM cards WHERE user_id = :user_id");
        $stmt->execute(["user_id" => $userId]);
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($card) => new CardModel($card), $cards);
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
        $sql = "UPDATE cards SET user_id = :newOwnerId WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':newOwnerId', $newOwnerId);
        $stmt->bindParam(':cardId', $cardId);
        return $stmt->execute();
    }

    public function setCardListedStatus(int $cardId, bool $isListed): bool {
        $stmt = $this->pdo->prepare("UPDATE cards SET is_listed = :is_listed, updated_at = NOW() WHERE id = :card_id");
        return $stmt->execute([
            'is_listed' => $isListed ? 1 : 0,
            'card_id' => $cardId
        ]);
    }
}