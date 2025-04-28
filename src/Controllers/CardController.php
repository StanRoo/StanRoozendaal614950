<?php

namespace App\Controllers;

use App\Services\CardService;
use App\Services\UserService;
use App\Middleware\AuthMiddleware;
use App\Utils\ResponseHelper;

class CardController {
    private $cardService;
    private $userService;
    private $authMiddleware;

    public function __construct(CardService $cardService, UserService $userService, AuthMiddleware $authMiddleware) {
        $this->cardService = $cardService;
        $this->userService = $userService;
        $this->authMiddleware = $authMiddleware;
    }

    public function createCard($userId): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken(); 
            $data = $_POST;
            $image = $_FILES['image'] ?? null;

            $user = $this->userService->getUserById($userId);
            if (!$user) {
                ResponseHelper::error('User not found.', 404);
                return;
            }

            $balance = $user->getBalance();
            $requiredBalance = $data['required_balance'] ?? null;

            if ($requiredBalance === null) {
                ResponseHelper::error('Something went wrong. Please try again later.', 400);
                return;
            }

            if ($balance < $requiredBalance) {
                ResponseHelper::error('Insufficient CuboCoins.', 400);
                return;
            }

            $result = $this->cardService->createCard($userId, $data, $image);

            if ($result['success']) {
                $newBalance = $balance - $requiredBalance;
                $this->userService->updateBalance($userId, $newBalance);

                ResponseHelper::success([
                    'new_balance' => $newBalance,
                    'card' => $result['data']
                ], 'Card created successfully.');
            } else {
                ResponseHelper::error('Failed to create card.', 500);
            }
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while creating the card: ' . $e->getMessage(), 500);
        }
    }

    public function getUserCards($userId): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            if ($decodedUser->id != $userId) {
                ResponseHelper::error('Unauthorized access.', 403);
                return;
            }

            $cards = $this->cardService->getUserCards($userId);

            ResponseHelper::success(['cards' => $cards], 'User cards retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while fetching user cards: ' . $e->getMessage(), 500);
        }
    }

    public function getCardById($cardId): void {
        try {
            $card = $this->cardService->getCardById($cardId);
            if (!$card) {
                ResponseHelper::error('Card not found.', 404);
                return;
            }

            ResponseHelper::success(['card' => $card], 'Card fetched successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while fetching the card: ' . $e->getMessage(), 500);
        }
    }

    public function deleteCard($cardId): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            $deleted = $this->cardService->deleteCard($decodedUser->id, $cardId);
            if (!$deleted) {
                ResponseHelper::error("Couldn't delete card: Unautorized, Card not found or Card is listed.", 400);
                return;
            }

            ResponseHelper::success(null, 'Card deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while deleting the card: ' . $e->getMessage(), 500);
        }
    }

    public function getAllCards(): void {
        try {
            $cards = $this->cardService->getAllCards();
            ResponseHelper::success(['cards' => $cards], 'All cards retrieved successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to fetch cards: ' . $e->getMessage(), 500);
        }
    }
    
    public function updateCard($id): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $this->cardService->updateCard($id, $input);

            ResponseHelper::success(null, 'Card updated successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to update card: ' . $e->getMessage(), 400);
        }
    }
    
    public function adminDeleteCard($id): void {
        try {
            $this->cardService->deleteCard($id);

            ResponseHelper::success(null, 'Card deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to delete card: ' . $e->getMessage(), 400);
        }
    }
}