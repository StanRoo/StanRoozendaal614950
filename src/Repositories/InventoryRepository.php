<?php

namespace App\Repositories;

class InventoryRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addCardToInventory($userId, $cardId): bool {
        $sql = "INSERT INTO inventory (user_id, card_id) VALUES (:userId, :cardId)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':cardId', $cardId);
        return $stmt->execute();
    }
}