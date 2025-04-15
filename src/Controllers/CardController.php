<?php

namespace App\Controllers;

use App\Services\CardService;
use App\Services\UserService;
use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;
use App\Utils\Validator;

class CardController {
    private $cardService;
    private $userService;
    private $authMiddleware;

    public function __construct(CardService $cardService, UserService $userService, AuthMiddleware $authMiddleware) {
        $this->cardService = $cardService;
        $this->userService = $userService;
        $this->authMiddleware = $authMiddleware;
    }

    public function createCard($userId) {
        $decodedUser = $this->authMiddleware->verifyToken(); 
        $data = $_POST;
        $image = $_FILES['image'] ?? null;
    
        $user = $this->userService->getUserById($userId);
        if (!$user) {
            ErrorHandler::respondWithError(404, "User not found.");
        }
        
        $balance = $user->getBalance();
        $requiredBalance = $data['required_balance'] ?? null;
    
        if ($requiredBalance === null) {
            ErrorHandler::respondWithError(400, "Missing required_balance field.");
        }
    
        if ($balance < $requiredBalance) {
            ErrorHandler::respondWithError(400, "Insufficient balance.");
        }
    
        $result = $this->cardService->createCard($userId, $data, $image);
    
        if ($result['success']) {
            $newBalance = $balance - $requiredBalance;
            $this->userService->updateBalance($userId, $newBalance);
    
            echo json_encode([
                "success" => true,
                "message" => "Card created successfully",
                "new_balance" => $newBalance,
                "card" => $result['data']
            ]);
        } else {
            ErrorHandler::respondWithError(500, "Failed to create card.");
        }
    }

    public function getUserCards($userId) {
        $decodedUser = $this->authMiddleware->verifyToken();
    
        if ($decodedUser->id != $userId) {
            ErrorHandler::respondWithError(403, "Unauthorized access.");
        }
    
        $cards = $this->cardService->getUserCards($userId);
        echo json_encode(["cards" => $cards]);
    }

    public function getCardById($cardId) {
        try {
            $card = $this->cardService->getCardById($cardId);
            if (!$card) {
                ErrorHandler::respondWithError(404, "Card not found.");
            }

            echo json_encode([
                "success" => true,
                "message" => "Card fetched successfully!",
                "card" => $card
            ]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }

    public function deleteCard($cardId) {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            $deleted = $this->cardService->deleteCard($decodedUser->id, $cardId);
            if (!$deleted) {
                ErrorHandler::respondWithError(403, "Unauthorized, card not found, or card is listed.");
            }

            echo json_encode(["success" => true, "message" => "Card deleted successfully!"]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }
}