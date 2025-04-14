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

    public function listCard($userId)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $cardId = $data['card_id'] ?? null;
        $price = $data['price'] ?? null;

        if (!$cardId || !$price) {
            return ErrorHandler::respondWithError(400, "Card ID and price are required.");
        }

        if (!is_numeric($price) || $price <= 0) {
            return ErrorHandler::respondWithError(400, "Price must be a positive number.");
        }

        $result = $this->marketplaceService->listCard($userId, $cardId, $price);

        if (isset($result['error'])) {
            return ErrorHandler::respondWithError($result['error']['status'], $result['error']['message']);
        }

        echo json_encode(["success" => true, "message" => "Card listed on the marketplace!"]);
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
            
            if (!$cardWithListing) {
                ErrorHandler::respondWithError(404, "Listing not found for card ID: $cardId");
            }
            echo json_encode([
                'card' => $cardWithListing['card'],
                'price' => $cardWithListing['price'],
                'listing_id' => $cardWithListing['listing_id'],
                'listed_at' => $cardWithListing['listed_at'],
                'seller_id' => $cardWithListing['seller_id'],
                'seller_username' => $cardWithListing['seller_username']
            ]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, $e->getMessage());
        }
    }
}