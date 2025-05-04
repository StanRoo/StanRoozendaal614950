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

    public function getMarketplaceCards($userId): void
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            if ($decodedUser->id != $userId) {
                ResponseHelper::error('Unauthorized access.', 403);
                return;
            }
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
            $filters = [
                'search' => $_GET['search'] ?? null,
                'rarity' => $_GET['rarity'] ?? null,
                'type' => $_GET['type'] ?? null,
                'sort' => $_GET['sort'] ?? 'lowest_price',
            ];
            $result = $this->marketplaceService->getFilteredListings($userId, $offset, $limit, $filters);
            if (!$result['success']) {
                ResponseHelper::error($result['message'], 500);
                return;
            }
            ResponseHelper::success([
                'listings' => $result['data'],
                'pagination' => $result['pagination']
            ], 'Marketplace listings retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while fetching marketplace listings: ' . $e->getMessage(), 500);
        }
    }

    public function getAllListings($decodedUser)
    {
        try {
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('You are not authorized to view listings.', 403);
                return;
            }

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $filters = [
                'card_id' => $_GET['card_id'] ?? null,
                'seller_id' => $_GET['seller_id'] ?? null,
                'listed_at' => $_GET['listed_at'] ?? null,
                'expires_at' => $_GET['expires_at'] ?? null,
                'status' => $_GET['status'] ?? null,
            ];

            $listingsResult = $this->marketplaceService->getAllListings($decodedUser, $page, $limit, $filters);

            if (!$listingsResult['success']) {
                ResponseHelper::error($listingsResult['message'], 500);
                return;
            }

            ResponseHelper::success([
                'listings' => $listingsResult['data'],
                'pagination' => $listingsResult['pagination']
            ], 'Listings fetched successfully.');

        } catch (\Exception $e) {
            ResponseHelper::error('Failed to fetch listings: ' . $e->getMessage(), 500);
        }
    }

    public function getUserListings($userId): void
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            if ($decodedUser->id != $userId) {
                ResponseHelper::error('Unauthorized access.', 403);
                return;
            }
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
            $filters = [
                'search'     => $_GET['search'] ?? null,
                'rarity'     => $_GET['rarity'] ?? null,
                'type'       => $_GET['type'] ?? null,
                'min_price'  => isset($_GET['min_price']) ? (float) $_GET['min_price'] : null,
                'max_price'  => isset($_GET['max_price']) ? (float) $_GET['max_price'] : null,
                'sort'       => $_GET['sort'] ?? 'lowest_price',
            ];
            $result = $this->marketplaceService->getUserFilteredListings($userId, $offset, $limit, $filters);
            if (!$result['success']) {
                ResponseHelper::error($result['message'], 500);
                return;
            }
            ResponseHelper::success([
                'listings'   => $result['data'],
                'pagination' => $result['pagination']
            ], 'Your listings retrieved successfully.');

        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while fetching your listings: ' . $e->getMessage(), 500);
        }
    }

    public function getMarketplaceCard($cardId)
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            $result = $this->marketplaceService->getCardWithListing($cardId);
            $cardWithListing = $result['data'];
            if (!$cardWithListing) {
                ResponseHelper::error("Listing not found for card ID: $cardId", 404);
                return;
            }
            $highestBidResult = $this->marketplaceService->getHighestBidForListing($cardWithListing['listing_id']);
            $highestBid = $highestBidResult['data'];
            $highestBidder = null;
            if ($highestBid) {
                $highestBidder = $this->marketplaceService->getHighestBidder($highestBid['bidder_id']);
            }
            ResponseHelper::success([
                'card' => $cardWithListing['card'],
                'price' => $cardWithListing['price'],
                'listing_id' => $cardWithListing['listing_id'],
                'listed_at' => $cardWithListing['listed_at'],
                'expires_at' => $cardWithListing['expires_at'],
                'seller_id' => $cardWithListing['seller_id'],
                'seller_username' => $cardWithListing['seller_username'],
                'min_bid_price' => $cardWithListing['min_bid_price'],
                'highest_bid' => $highestBid,
                'highest_bid_username' => $highestBidder ? $highestBidder->getUsername() : null,
            ], 'Card details retrieved successfully.');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
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

    public function finalizeExpiredListings()
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('Access denied. Admins only.', 403);
                return;
            }
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