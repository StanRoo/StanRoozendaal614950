<?php

namespace App\Repositories;

use App\Models\MarketplaceListingModel;
use PDO;

class MarketplaceRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createListing(MarketplaceListingModel $listing): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("
            INSERT INTO marketplace_listings (card_id, seller_id, price, listed_at, status)
            VALUES (:card_id, :seller_id, :price, :listed_at, :status)
        ");
        $stmt->execute([
            'card_id' => $listing->getCardId(),
            'seller_id' => $listing->getSellerId(),
            'price' => $listing->getPrice(),
            'listed_at' => $listing->getListedAt(),
            'status' => $listing->getStatus()
        ]);
        $id = $this->pdo->lastInsertId();
        return $this->getId($id);
    }

    public function getListingById($listingId) {
        $sql = "SELECT * FROM marketplace_listings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $listingId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveListings(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE status = 'active'");
        $stmt->execute();
        $listings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $listings[] = new MarketplaceListingModel($row);
        }
        return $listings;
    }

    public function getActiveListingByCardId($cardId): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE card_id = :card_id AND status = 'active' LIMIT 1");
        $stmt->execute(['card_id' => $cardId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new MarketplaceListingModel($row) : null;
    }

    public function markListingAsSold($listingId): bool {
        $sql = "UPDATE marketplace_listings SET status = 'sold' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $listingId);
        return $stmt->execute();
    }

    public function cancelListing(int $id): void {
        $stmt = $this->pdo->prepare("UPDATE marketplace_listings SET status = 'cancelled' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getListingByCardId(int $cardId): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE card_id = :card_id AND status = 'active' LIMIT 1");
        $stmt->execute(['card_id' => $cardId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return new MarketplaceListingModel($data);
    }
}