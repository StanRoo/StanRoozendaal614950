<?php

namespace App\Repositories;

use App\Models\MarketplaceListingModel;
use PDO;

class MarketplaceRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // -------- GET --------
    public function getListingByCardId(int $cardId): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE card_id = :card_id AND status = 'active' LIMIT 1");
        $stmt->execute(['card_id' => $cardId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return new MarketplaceListingModel($data);
    }

    public function getExpiredActiveListings(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= NOW()");
        $stmt->execute();
        $expired = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $expired[] = new MarketplaceListingModel($row);
        }
        return $expired;
    }

    public function getAllListings(int $limit, int $offset, array $filters = []): array
    {
        $sql = "
            SELECT ml.*, u.username AS seller_username, c.name AS card_name
            FROM marketplace_listings ml
            JOIN users u ON ml.seller_id = u.id
            JOIN cards c ON ml.card_id = c.id
        ";
        $where = [];
        $params = [];
        if (!empty($filters['card_id'])) {
            $where[] = 'ml.card_id = :card_id';
            $params[':card_id'] = $filters['card_id'];
        }
        if (!empty($filters['seller_id'])) {
            $where[] = 'ml.seller_id = :seller_id';
            $params[':seller_id'] = $filters['seller_id'];
        }
        if (!empty($filters['listed_at'])) {
            $direction = strtoupper($filters['listed_at']) === 'ASC' ? 'ASC' : 'DESC';
            $where[] = "ml.listed_at IS NOT NULL";
        }
        if (!empty($filters['expires_at'])) {
            $direction = strtoupper($filters['expires_at']) === 'ASC' ? 'ASC' : 'DESC';
            $where[] = "ml.expires_at IS NOT NULL";
        }
        if (!empty($filters['status'])) {
            $where[] = 'ml.status = :status';
            $params[':status'] = $filters['status'];
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if (!empty($filters['expires_at'])) {
            $sql .= " ORDER BY ml.expires_at " . strtoupper($filters['expires_at']);
        } elseif (!empty($filters['listed_at'])) {
            $sql .= " ORDER BY ml.listed_at " . strtoupper($filters['listed_at']);
        } else {
            $sql .= " ORDER BY ml.listed_at DESC";
        }
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $listingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listings = [];
        foreach ($listingsData as $data) {
            $listings[] = new MarketplaceListingModel($data);
        }
        return $listings;
    }

    public function getListingsCount(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM marketplace_listings ml
                JOIN users u ON ml.seller_id = u.id
                JOIN cards c ON ml.card_id = c.id";
        $where = [];
        $params = [];
        if (!empty($filters['card_id'])) {
            $where[] = 'ml.card_id = :card_id';
            $params[':card_id'] = $filters['card_id'];
        }
        if (!empty($filters['seller_id'])) {
            $where[] = 'ml.seller_id = :seller_id';
            $params[':seller_id'] = $filters['seller_id'];
        }
        if (!empty($filters['listed_at'])) {
            $where[] = "ml.listed_at IS NOT NULL";
        }
        if (!empty($filters['expires_at'])) {
            $where[] = "ml.expires_at IS NOT NULL";
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

    public function getListingById(int $listingId): ?MarketplaceListingModel {
        $sql = "SELECT * FROM marketplace_listings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $listingId);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new MarketplaceListingModel($data) : null;
    }

    public function getFilteredListings(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        $sql = "SELECT marketplace_listings.* 
                FROM marketplace_listings 
                JOIN cards ON cards.id = marketplace_listings.card_id
                WHERE marketplace_listings.status = 'active' AND marketplace_listings.seller_id != :user_id";
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'cards.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'cards.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'cards.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['price_min'])) {
            $where[] = 'marketplace_listings.price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where[] = 'marketplace_listings.price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $sql .= ' ORDER BY marketplace_listings.price ASC';
                    break;
                case 'price_desc':
                    $sql .= ' ORDER BY marketplace_listings.price DESC';
                    break;
                case 'listed_asc':
                    $sql .= ' ORDER BY marketplace_listings.listed_at ASC';
                    break;
                case 'listed_desc':
                default:
                    $sql .= ' ORDER BY marketplace_listings.listed_at DESC';
                    break;
            }
        } else {
            $sql .= ' ORDER BY marketplace_listings.listed_at DESC';
        }
        $sql .= ' LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => new \App\Models\MarketplaceListingModel($row), $results);
    }

    public function getFilteredListingsCount(int $userId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) 
                FROM marketplace_listings 
                JOIN cards ON cards.id = marketplace_listings.card_id
                WHERE marketplace_listings.status = 'active' AND marketplace_listings.seller_id != :user_id";
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'cards.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'cards.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'cards.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['price_min'])) {
            $where[] = 'marketplace_listings.price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where[] = 'marketplace_listings.price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
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

    public function getFilteredUserListings(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        $sql = "SELECT marketplace_listings.* 
                FROM marketplace_listings 
                JOIN cards ON cards.id = marketplace_listings.card_id
                WHERE marketplace_listings.status = 'active' AND marketplace_listings.seller_id = :user_id";
        
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'cards.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'cards.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'cards.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['price_min'])) {
            $where[] = 'marketplace_listings.price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where[] = 'marketplace_listings.price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
        }
        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $sql .= ' ORDER BY marketplace_listings.price ASC';
                    break;
                case 'price_desc':
                    $sql .= ' ORDER BY marketplace_listings.price DESC';
                    break;
                case 'listed_asc':
                    $sql .= ' ORDER BY marketplace_listings.listed_at ASC';
                    break;
                case 'listed_desc':
                default:
                    $sql .= ' ORDER BY marketplace_listings.listed_at DESC';
                    break;
            }
        } else {
            $sql .= ' ORDER BY marketplace_listings.listed_at DESC';
        }
        $sql .= ' LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => new \App\Models\MarketplaceListingModel($row), $results);
    }

    public function getFilteredUserListingsCount(int $userId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*) 
                FROM marketplace_listings 
                JOIN cards ON cards.id = marketplace_listings.card_id
                WHERE marketplace_listings.status = 'active' AND marketplace_listings.seller_id = :user_id";
        $params = [':user_id' => $userId];
        $where = [];
        if (!empty($filters['search'])) {
            $where[] = 'cards.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['rarity'])) {
            $where[] = 'cards.rarity = :rarity';
            $params[':rarity'] = $filters['rarity'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'cards.type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['price_min'])) {
            $where[] = 'marketplace_listings.price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $where[] = 'marketplace_listings.price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
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

    public function getActiveListingByCardId($cardId): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("SELECT * FROM marketplace_listings WHERE card_id = :card_id AND status = 'active' LIMIT 1");
        $stmt->execute(['card_id' => $cardId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new MarketplaceListingModel($row) : null;
    }

    // -------- UPDATE --------
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

    public function updateListing(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE marketplace_listings
            SET status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id,
            ':status' => $data['status']
        ]);
    }

    // -------- POST ---------
    public function createListing(MarketplaceListingModel $listing): ?MarketplaceListingModel {
        $stmt = $this->pdo->prepare("
            INSERT INTO marketplace_listings (card_id, seller_id, price, listed_at, status, min_bid_price, expires_at) VALUES (
                :card_id, :seller_id, :price, :listed_at, :status, :min_bid_price, :expires_at
            )
        ");

        $stmt->execute([
            'card_id' => $listing->getCardId(),
            'seller_id' => $listing->getSellerId(),
            'price' => $listing->getPrice(),
            'listed_at' => $listing->getListedAt(),
            'status' => $listing->getStatus(),
            'min_bid_price' => $listing->getMinBidPrice(),
            'expires_at' => $listing->getExpiresAt(),
        ]);
        $id = $this->pdo->lastInsertId();
        return new MarketplaceListingModel(array_merge($listing->toArray(), ['id' => $id]));
    }

    // -------- DELETE ---------
    public function deleteListing(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM marketplace_listings WHERE id = ?");
        return $stmt->execute([$id]);
    }
}