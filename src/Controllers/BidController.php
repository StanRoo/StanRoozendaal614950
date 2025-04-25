<?php

namespace App\Controllers;

use App\Models\BidModel;
use App\Services\BidService;
use App\Services\MarketplaceService;

class BidController {
    private BidService $bidService;
    private MarketplaceService $marketplaceService;

    public function __construct(BidService $bidService, MarketplaceService $marketplaceService) {
        $this->bidService = $bidService;
        $this->marketplaceService = $marketplaceService;
    }

    public function placeBid($userId): void {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $listingId = $data['listing_id'] ?? null;
        $bidAmount = $data['amount'] ?? null;
    
        if (!$listingId || !$bidAmount || !$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            return;
        }
    
        $listing = $this->marketplaceService->getListingById($listingId);
    
        if (!$listing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Listing not found.']);
            return;
        }
    
        if (strtotime($listing->expires_at) < time()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Bidding for this listing has ended.']);
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
    
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getBidsForListing(): void {
        $listingId = $_GET['listing_id'] ?? null;

        if (!$listingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Listing ID required']);
            return;
        }

        $bids = $this->bidService->getAllBidsForListing((int)$listingId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'bids' => $bids]);
    }

    public function getMyBids(): void {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $bids = $this->bidService->getBidsByUser((int)$userId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'bids' => $bids]);
    }

    public function getAllBids() {
        $bids = $this->bidService->getAllBids();
        echo json_encode(['bids' => $bids]);
    }

    public function deleteBid(int $id) {
        try {
            $this->bidService->deleteBid($id);
            echo json_encode(['message' => 'Bid deleted successfully']);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }
}