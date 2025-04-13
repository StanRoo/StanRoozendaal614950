<?php

namespace App\Services;

use App\Repositories\InventoryRepository;

class InventoryService {
    private $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository) {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function addCardToInventory($userId, $cardId): bool {
        return $this->inventoryRepository->addCardToInventory($userId, $cardId);
    }
}