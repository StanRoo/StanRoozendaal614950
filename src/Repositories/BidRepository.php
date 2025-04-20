<?php

namespace App\Repositories;

use App\Models\BidModel;
use PDO;

class BidRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

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
}