<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Services\UserService;
use App\Services\CardService;
use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;

class TransactionController {
    private $transactionService;
    private $userService;
    private $cardService;
    private $authMiddleware;

    public function __construct(TransactionService $transactionService, UserService $userService, CardService $cardService, AuthMiddleware $authMiddleware) {
        $this->transactionService = $transactionService;
        $this->userService = $userService;
        $this->cardService = $cardService;
        $this->authMiddleware = $authMiddleware;
    }

    public function buyCard($userId) {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            if ($decodedUser->id !== $userId) {
                ErrorHandler::respondWithError(403, "Unauthorized");
            }

            $requestData = json_decode(file_get_contents("php://input"), true);
            if (!isset($requestData['card_id'])) {
                ErrorHandler::respondWithError(400, "Card ID is required.");
            }

            $card = $this->cardService->getCardById($requestData['card_id']);
            if (!$card) {
                ErrorHandler::respondWithError(404, "Card not found.");
            }

            $user = $this->userService->getUserById($userId);
            if (!$user) {
                ErrorHandler::respondWithError(404, "User not found.");
            }

            if ($user->getBalance() < $card->getPrice()) {
                ErrorHandler::respondWithError(400, "Insufficient balance.");
            }

            $purchase = $this->transactionService->buyCard($userId, $requestData['card_id']);

            echo json_encode([
                "success" => true,
                "message" => "Card purchased successfully!",
                "transaction" => $purchase
            ]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
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