<?php

namespace App\Controllers;

use App\Models\BidModel;
use App\Services\BidService;
use App\Services\MarketplaceService;
use App\Utils\ResponseHelper;

class BidController {
    private BidService $bidService;
    private MarketplaceService $marketplaceService;

    public function __construct(BidService $bidService, MarketplaceService $marketplaceService) {
        $this->bidService = $bidService;
        $this->marketplaceService = $marketplaceService;
    }

    public function placeBid($userId): void {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $listingId = $data['listing_id'] ?? null;
            $bidAmount = $data['amount'] ?? null;

            if (!$listingId || !$bidAmount || !$userId) {
                ResponseHelper::error('Something went wrong.', 400);
                return;
            }

            $listing = $this->marketplaceService->getListingById($listingId);

            if (!$listing) {
                ResponseHelper::error('Listing not found.', 404);
                return;
            }

            if (strtotime($listing->expires_at) < time()) {
                ResponseHelper::error('Listing has ended.', 400);
                return;
            }

            $minimumBid = 10.00;

            $bid = new BidModel([
                'listing_id' => (int)$listingId,
                'bidder_id' => (int)$userId,
                'bid_amount' => (float)$bidAmount,
                'bid_time' => date('Y-m-d H:i:s'),
            ]);

            $result = $this->bidService->placeBid($bid, $minimumBid);

            if (isset($result['error'])) {
                ResponseHelper::error($result['message'], 400);
            } else {
                ResponseHelper::success($result, 'Bid placed successfully.');
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while placing the bid: " . $e->getMessage(), 500);
        }
    }

    public function getBidsForListing(): void {
        try {
            $listingId = $_GET['listing_id'] ?? null;

            if (!$listingId) {
                ResponseHelper::error('Something went wrong.', 400);
                return;
            }

            $bids = $this->bidService->getAllBidsForListing((int)$listingId);

            ResponseHelper::success(['bids' => $bids], 'Bids retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while fetching bids: " . $e->getMessage(), 500);
        }
    }

    public function getMyBids(): void {
        try {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                ResponseHelper::error('Not logged in.', 401);
                return;
            }

            $bids = $this->bidService->getBidsByUser((int)$userId);

            ResponseHelper::success(['bids' => $bids], 'Your bids retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while fetching your bids: " . $e->getMessage(), 500);
        }
    }

    public function getAllBids(): void {
        try {
            $bids = $this->bidService->getAllBids();
            ResponseHelper::success(['bids' => $bids], 'All bids retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while fetching all bids: " . $e->getMessage(), 500);
        }
    }

    public function deleteBid(int $id): void {
        try {
            $this->bidService->deleteBid($id);
            ResponseHelper::success(null, 'Bid deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error("Failed to delete bid: " . $e->getMessage(), 500);
        }
    }
}