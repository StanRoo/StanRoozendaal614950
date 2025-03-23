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
        $stmt = $this->pdo->prepare("SELECT id, user_id, name, rarity, price, image_url, created_at FROM cards WHERE id = ?");
        $stmt->execute([$id]);
        $cardData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $cardData ? new CardModel($cardData) : null;
    }

    public function getAllCards($userId = null): array {
        $sql = "SELECT id, user_id, name, rarity, price, image_url, created_at FROM cards";
        if ($userId) {
            $sql .= " WHERE user_id = ?";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($userId ? [$userId] : []);
        return array_map(fn($row) => new CardModel($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(CardModel $card): ?CardModel {
        $stmt = $this->pdo->prepare("INSERT INTO cards (user_id, name, rarity, price, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$card->user_id, $card->name, $card->rarity, $card->price, $card->image_url]);
        return $this->getCardById($this->pdo->lastInsertId());
    }

    public function delete($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cards WHERE id = ?");
        return $stmt->execute([$id]);
    }
}