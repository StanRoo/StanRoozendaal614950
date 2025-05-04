<?php

namespace App\Repositories;

use App\Models\BidModel;
use PDO;

class BidRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // --------- GET ----------
    public function getHighestBidByListingId(int $listingId): ?BidModel {
        $sql = "SELECT * FROM bids WHERE listing_id = :listing_id ORDER BY bid_amount DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':listing_id', $listingId);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new BidModel($data) : null;
    }

    public function getAllBidsForListing(int $listingId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM bids WHERE listing_id = :listing_id ORDER BY bid_amount DESC");
        $stmt->execute(['listing_id' => $listingId]);

        $bids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bids[] = new BidModel($row);
        }
        return $bids;
    }

    public function getBidsByUserId(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM bids WHERE bidder_id = :bidder_id ORDER BY bid_time DESC");
        $stmt->execute(['bidder_id' => $userId]);

        $bids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bids[] = new BidModel($row);
        }
        return $bids;
    }

    public function getAllBids(int $limit, int $offset, array $filters = []): array
    {
        $sql = "SELECT b.id, b.listing_id, b.bidder_id, b.bid_amount, b.bid_time
                FROM bids b";
        $where = [];
        $params = [];

        if (!empty($filters['listing_id'])) {
            $where[] = 'b.listing_id = :listing_id';
            $params[':listing_id'] = $filters['listing_id'];
        }

        if (!empty($filters['bidder_id'])) {
            $where[] = 'b.bidder_id = :bidder_id';
            $params[':bidder_id'] = $filters['bidder_id'];
        }

        if (!empty($filters['created_at'])) {
            if ($filters['created_at'] === 'ascending') {
                $where[] = 'b.bid_time ASC';
            } else if ($filters['created_at'] === 'descending') {
                $where[] = 'b.bid_time DESC';
            }
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY b.bid_time DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $bidsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $bids = [];
        foreach ($bidsData as $data) {
            $bids[] = new BidModel($data);
        }

        return $bids;
    }

    public function getBidsCount(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM bids";
        $where = [];
        $params = [];

        if (!empty($filters['listing_id'])) {
            $where[] = 'listing_id = :listing_id';
            $params[':listing_id'] = $filters['listing_id'];
        }

        if (!empty($filters['bidder_id'])) {
            $where[] = 'bidder_id = :bidder_id';
            $params[':bidder_id'] = $filters['bidder_id'];
        }

        if (!empty($filters['created_at'])) {
            if ($filters['created_at'] === 'ascending') {
                $where[] = 'created_at >= :created_at';
            } else if ($filters['created_at'] === 'descending') {
                $where[] = 'created_at <= :created_at';
            }
            $params[':created_at'] = date('Y-m-d H:i:s');
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

    // --------- POST ----------
    public function createBid(BidModel $bid): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO bids (listing_id, bidder_id, bid_amount, bid_time)
            VALUES (:listing_id, :bidder_id, :bid_amount, NOW())
        ");
        return $stmt->execute([
            'listing_id' => $bid->getListingId(),
            'bidder_id' => $bid->getBidderId(),
            'bid_amount' => $bid->getBidAmount()
        ]);
    }

    // --------- DELETE ----------
    public function deleteBid(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM bids WHERE id = ?");
        return $stmt->execute([$id]);
    }
}