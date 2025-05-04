<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\BidModel;
use App\Services\BidService;
use App\Services\MarketplaceService;
use App\Utils\ResponseHelper;

class BidController {
    private BidService $bidService;
    private MarketplaceService $marketplaceService;
    private AuthMiddleware $authMiddleware;

    public function __construct(BidService $bidService, MarketplaceService $marketplaceService, AuthMiddleware $authMiddleware) {
        $this->bidService = $bidService;
        $this->marketplaceService = $marketplaceService;
        $this->authMiddleware = $authMiddleware;
    }

    public function getAllBids(): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
    
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('Access denied. Admins only.', 403);
                return;
            }
    
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    
            $filters = [
                'listing_id' => $_GET['listing_id'] ?? null,
                'bidder_id' => $_GET['bidder_id'] ?? null,
                'created_at' => $_GET['created_at'] ?? null,
            ];

            $bidsResult = $this->bidService->getAllBids($decodedUser, $page, $limit, $filters);

            if (!$bidsResult['success']) {
                ResponseHelper::error($bidsResult['message'], 500);
                return;
            }

            ResponseHelper::success([
                'bids' => $bidsResult['data'],
                'pagination' => $bidsResult['pagination']
            ], 'Bids fetched successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while fetching bids: " . $e->getMessage(), 500);
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

            if (!$result['success']) {
                ResponseHelper::error($result['message'], 400);
            } else {
                ResponseHelper::success($result, 'Bid placed successfully.');
            }
        } catch (\Throwable $e) {
            ResponseHelper::error("An error occurred while placing the bid: " . $e->getMessage(), 500);
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