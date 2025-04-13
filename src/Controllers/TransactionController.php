<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Services\MarketplaceService;
use App\Services\InventoryService;
use App\Services\UserService;
use App\Services\CardService;
use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;

class TransactionController {
    private $transactionService;
    private $marketplaceService;
    private $inventoryService;
    private $userService;
    private $cardService;
    private $authMiddleware;

    public function __construct(TransactionService $transactionService, MarketplaceService $marketplaceService, InventoryService $inventoryService, UserService $userService, CardService $cardService, AuthMiddleware $authMiddleware) {
        $this->transactionService = $transactionService;
        $this->marketplaceService = $marketplaceService;
        $this->inventoryService = $inventoryService;
        $this->userService = $userService;
        $this->cardService = $cardService;
        $this->authMiddleware = $authMiddleware;
    }

    public function buyCard($listingId, $buyerId) {
        $listing = $this->marketplaceService->getListingById($listingId);
        if (!$listing) {
            ErrorHandler::respondWithError(404, "Listing not found.");
            return;
        }

        $cardId = $listing['card_id'];
        $sellerId = $listing['seller_id'];
        $price = $listing['price'];
        $buyer = $this->userService->getUserById($buyerId);
        if ($buyer->balance < $price) {
            ErrorHandler::respondWithError(400, "Insufficient balance.");
            return;
        }
        $this->userService->updateBalance($buyer->id, $price);
        $this->userService->addBalance($sellerId, $price);
        $this->marketplaceService->markListingAsSold($listingId);
        $this->inventoryService->addCardToInventory($buyerId, $cardId);
        $transactionCreated = $this->transactionService->logTransaction($buyerId, $sellerId, $cardId, $price);
        if ($transactionCreated) {
            echo json_encode(['message' => 'Transaction successful']);
        } else {
            ErrorHandler::respondWithError(500, 'Transaction failed.');
        }
    }

    public function getUserInventory($userId) {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            if ($decodedUser->id !== $userId) {
                ErrorHandler::respondWithError(403, "Unauthorized");
            }

            $inventory = $this->transactionService->getUserInventory($userId);

            echo json_encode([
                "success" => true,
                "message" => "Inventory fetched successfully!",
                "inventory" => $inventory
            ]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }
}