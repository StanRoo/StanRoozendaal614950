<?php

namespace App\Controllers;

use App\Services\MarketplaceService;
use App\Utils\ErrorHandler;
use App\Utils\Validator;

class MarketplaceController
{
    private MarketplaceService $marketplaceService;
    private $authMiddleware;
    private $errorHandler;

    public function __construct(MarketplaceService $marketplaceService, $authMiddleware, $errorHandler)
    {
        $this->marketplaceService = $marketplaceService;
        $this->authMiddleware = $authMiddleware;
        $this->errorHandler = $errorHandler;
    }

    public function listCard($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        $cardId = $data['card_id'] ?? null;
        $price = $data['price'] ?? null;
        $minimumBid = $data['min_bid_price'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        if (!$cardId || !$price || !$minimumBid || !$expiresAt) {
            return ErrorHandler::respondWithError(400, "Card ID, price, minimum bid, and expiry date are required.");
        }
        if (!is_numeric($price) || $price <= 0 || !is_numeric($minimumBid) || $minimumBid < 0) {
            return ErrorHandler::respondWithError(400, "Price and minimum bid must be valid numbers (min bid can be 0).");
        }
        if (!strtotime($expiresAt)) {
            return ErrorHandler::respondWithError(400, "Invalid expiry date.");
        }
        $result = $this->marketplaceService->listCard($userId, $cardId, $price, $minimumBid, $expiresAt);
        if (!$result['success']) {
            return ErrorHandler::respondWithError(500, $result['message']);
        }
        echo json_encode([
            "success" => true,
            "message" => $result['message'],
            "listing" => $result['data']
        ]);
    }

    public function getMarketplaceCards($user_id) {
        $listings = $this->marketplaceService->getAllActiveListingsExceptUser($user_id);
    
        if (empty($listings)) {
            http_response_code(404);
            echo json_encode(['message' => 'No cards listed on the marketplace']);
            return;
        }
    
        $listingsData = array_map(function ($listing) {
            return [
                'id' => $listing->getId(),
                'card_id' => $listing->getCardId(),
                'seller_id' => $listing->getSellerId(),
                'price' => $listing->getPrice(),
                'listed_at' => $listing->getListedAt(),
                'status' => $listing->getStatus(),
            ];
        }, $listings);
    
        echo json_encode(['listings' => $listingsData]);
    }

    public function getUserListings($userId) {
        try {
            $listings = $this->marketplaceService->getUserListings($userId);

            if (empty($listings)) {
                http_response_code(404);
                echo json_encode(['message' => 'You have no active listings.']);
                return;
            }

            $listingsData = array_map(function ($listing) {
                return [
                    'id' => $listing->getId(),
                    'card_id' => $listing->getCardId(),
                    'seller_id' => $listing->getSellerId(),
                    'price' => $listing->getPrice(),
                    'listed_at' => $listing->getListedAt(),
                    'status' => $listing->getStatus(),
                ];
            }, $listings);
            echo json_encode(['listings' => $listingsData]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, 'Failed to fetch your listings.');
        }
    }


    public function getMarketplaceCard($cardId) {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            $cardWithListing = $this->marketplaceService->getCardWithListing($cardId);
            $highestBid = $this->marketplaceService->getHighestBidForListing($cardWithListing['listing_id']);
            
            if (!$cardWithListing) {
                ErrorHandler::respondWithError(404, "Listing not found for card ID: $cardId");
            }
            echo json_encode([
                'card' => $cardWithListing['card'],
                'price' => $cardWithListing['price'],
                'listing_id' => $cardWithListing['listing_id'],
                'listed_at' => $cardWithListing['listed_at'],
                'seller_id' => $cardWithListing['seller_id'],
                'seller_username' => $cardWithListing['seller_username'],
                'min_bid_price' => $cardWithListing['min_bid_price'],
                'highest_bid' => $highestBid
            ]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, $e->getMessage());
        }
    }

    public function finalizeExpiredListings() {
        try {
            $results = $this->marketplaceService->finalizeExpiredListings();
            echo json_encode([
                'success' => true,
                'processed' => count($results),
                'results' => $results
            ]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, "Failed to finalize expired listings: " . $e->getMessage());
        }
    }

    public function buyCard($listingId, $buyerId) {
        $transactionResult = $this->marketplaceService->buyCard($listingId, $buyerId);
        if ($transactionResult['success']) {
            echo json_encode(['message' => 'Transaction successful']);
        } else {
            ErrorHandler::respondWithError(500, $transactionResult['message']);
        }
    }

    public function getAllListings($decodedUser) {
        try {
            if ($decodedUser->role !== 'admin') {
                ErrorHandler::respondWithError(403, 'You are not authorized to view listings.');
                return;
            }
            $listings = $this->marketplaceService->getAllListings();
            echo json_encode(['listings' => $listings]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, 'Failed to fetch listings: ' . $e->getMessage());
        }
    }
    
    public function updateListing($decodedUser, $listingId) {
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            if ($decodedUser->role !== 'admin') {
                ErrorHandler::respondWithError(403, 'You are not authorized to update listing status.');
                return;
            }
            $this->marketplaceService->updateListing($listingId, $input);
            echo json_encode(['message' => 'Listing status updated successfully']);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(400, 'Failed to update listing status');
        }
    }
    
    public function deleteListing($decodedUser, $listingId) {
        try {
            if ($decodedUser->role !== 'admin') {
                ErrorHandler::respondWithError(403, 'You are not authorized to delete listings.');
                return;
            }
            $this->marketplaceService->deleteListing($listingId);
            echo json_encode(['message' => 'Listing deleted successfully']);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(400, 'Failed to delete listing');
        }
    }
    
}