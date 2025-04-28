<?php

namespace App\Controllers;

use App\Services\MarketplaceService;
use App\Utils\ResponseHelper;
use App\Utils\Validator;
use App\Utils\ErrorHandler;

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

    public function listCard($userId)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $cardId = $data['card_id'] ?? null;
        $price = $data['price'] ?? null;
        $minimumBid = $data['min_bid_price'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        if (!$cardId || !$price || !$minimumBid || !$expiresAt) {
            ResponseHelper::error("Card ID, Price, Minimum Bid and Expire Date are required.", 400);
            return;
        }
        if (!is_numeric($price) || $price <= 0 || !is_numeric($minimumBid) || $minimumBid < 0) {
            ResponseHelper::error("Price or Mimimum Bid cannot be negative numbers.", 400);
            return;
        }
        if (!strtotime($expiresAt)) {
            ResponseHelper::error("Invalid Expiry Date.", 400);
            return;
        }

        $result = $this->marketplaceService->listCard($userId, $cardId, $price, $minimumBid, $expiresAt);

        if (!$result['success']) {
            ResponseHelper::error($result['message'], 500);
            return;
        }

        ResponseHelper::success([
            'listing' => $result['data']
        ], $result['message']);
    }

    public function getMarketplaceCards($userId)
    {
        $listings = $this->marketplaceService->getAllActiveListingsExceptUser($userId);

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

        ResponseHelper::success([
            'listings' => $listingsData
        ], empty($listingsData) ? "No cards listed on the marketplace." : "Marketplace listings retrieved successfully.");
    }

    public function getUserListings($userId)
    {
        try {
            $listings = $this->marketplaceService->getUserListings($userId);

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

            ResponseHelper::success([
                'listings' => $listingsData
            ], empty($listingsData) ? "You have no active listings." : "Your listings retrieved successfully.");
        } catch (\Exception $e) {
            ResponseHelper::error('Failed to fetch your listings.', 500);
        }
    }

    public function getMarketplaceCard($cardId)
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            $cardWithListing = $this->marketplaceService->getCardWithListing($cardId);

            if (!$cardWithListing) {
                ResponseHelper::error("Listing not found for card ID: $cardId", 404);
                return;
            }

            $highestBid = $this->marketplaceService->getHighestBidForListing($cardWithListing['listing_id']);

            ResponseHelper::success([
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
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function finalizeExpiredListings()
    {
        try {
            $results = $this->marketplaceService->finalizeExpiredListings();
            ResponseHelper::success([
                'processed' => count($results),
                'results' => $results
            ], "Expired listings finalized successfully.");
        } catch (\Exception $e) {
            ResponseHelper::error("Failed to finalize expired listings: " . $e->getMessage(), 500);
        }
    }

    public function buyCard($listingId, $buyerId)
    {
        $transactionResult = $this->marketplaceService->buyCard($listingId, $buyerId);

        if ($transactionResult['success']) {
            ResponseHelper::success(null, 'Transaction successful');
        } else {
            ResponseHelper::error($transactionResult['message'], 500);
        }
    }

    public function getAllListings($decodedUser)
    {
        try {
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('You are not authorized to view listings.', 403);
                return;
            }

            $listings = $this->marketplaceService->getAllListings();

            ResponseHelper::success([
                'listings' => $listings
            ]);
        } catch (\Exception $e) {
            ResponseHelper::error('Failed to fetch listings: ' . $e->getMessage(), 500);
        }
    }

    public function updateListing($decodedUser, $listingId)
    {
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('You are not authorized to update listing status.', 403);
                return;
            }

            $this->marketplaceService->updateListing($listingId, $input);

            ResponseHelper::success(null, 'Listing status updated successfully.');
        } catch (\Exception $e) {
            ResponseHelper::error('Failed to update listing status', 400);
        }
    }

    public function deleteListing($decodedUser, $listingId)
    {
        try {
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('You are not authorized to delete listings.', 403);
                return;
            }

            $this->marketplaceService->deleteListing($listingId);

            ResponseHelper::success(null, 'Listing deleted successfully.');
        } catch (\Exception $e) {
            ResponseHelper::error('Failed to delete listing', 400);
        }
    }
}