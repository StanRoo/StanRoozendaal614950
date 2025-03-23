<?php

namespace App\Controllers;

use App\Services\CardService;
use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;
use App\Utils\Validator;

class CardController {
    private $cardService;
    private $authMiddleware;

    public function __construct(CardService $cardService, AuthMiddleware $authMiddleware) {
        $this->cardService = $cardService;
        $this->authMiddleware = $authMiddleware;
    }

    public function createCard($userId) {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            
            if ($decodedUser->id !== $userId) {
                ErrorHandler::respondWithError(403, "Unauthorized");
            }

            $requestData = json_decode(file_get_contents("php://input"), true);
            if (!$requestData) {
                ErrorHandler::respondWithError(400, "Invalid request data.");
            }

            $nameValidation = Validator::validateCardName($requestData['name']);
            if ($nameValidation !== true) {
                ErrorHandler::respondWithError(400, $nameValidation);
            }

            $card = $this->cardService->createCard($userId, $requestData);
            echo json_encode([
                "success" => true,
                "message" => "Card created successfully!",
                "card" => $card
            ]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }

    public function getAllCards() {
        try {
            $cards = $this->cardService->getAllCards();
            echo json_encode([
                "success" => true,
                "message" => "Cards fetched successfully!",
                "cards" => $cards
            ]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
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

            $deleted = $this->cardService->deleteCard($cardId, $decodedUser);
            if (!$deleted) {
                ErrorHandler::respondWithError(403, "Unauthorized or card not found.");
            }

            echo json_encode(["success" => true, "message" => "Card deleted successfully!"]);
        } catch (\Exception $e) {
            ErrorHandler::handleException($e);
        }
    }
}