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

    public function getAllCards(): void
    {
        $decodedUser = $this->authMiddleware->verifyToken();

        if ($decodedUser->role !== 'admin') {
            ResponseHelper::error('Access denied. Admins only.', 403);
            return;
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

        $filters = [
            'id' => $_GET['id'] ?? null,
            'owner_id' => $_GET['owner_id'] ?? null,
            'name' => $_GET['name'] ?? null,
            'rarity' => $_GET['rarity'] ?? null,
            'is_listed' => isset($_GET['is_listed']) ? filter_var($_GET['is_listed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            'created_at' => $_GET['created_at'] ?? null,
        ];

        $cardsResult = $this->cardService->getAllCards($decodedUser, $page, $limit, $filters);

        if (!$cardsResult['success']) {
            ResponseHelper::error($cardsResult['message'], 500);
            return;
        }

        ResponseHelper::success([
            'cards' => $cardsResult['data'],
            'pagination' => $cardsResult['pagination']
        ], 'Cards fetched successfully.');
    }

    public function getUserCards($userId): void
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
                'sort' => $_GET['sort'] ?? 'name_asc',
            ];
            $cardsResult = $this->cardService->getUserCards($userId, $offset, $limit, $filters);
            if (!$cardsResult['success']) {
                ResponseHelper::error($cardsResult['message'], 500);
                return;
            }
            ResponseHelper::success([
                'cards' => $cardsResult['data'],
                'pagination' => $cardsResult['pagination']
            ], 'User cards retrieved successfully.');
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
    
    public function updateCard($id): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $this->cardService->updateCard($id, $input);

            ResponseHelper::success(null, 'Card updated successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to update card: ' . $e->getMessage(), 400);
        }
    }

    public function createCard($userId): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken(); 
            $data = $_POST;
            $image = $_FILES['image'] ?? null;

            $userResult = $this->userService->getUserById($userId);
            if (!$userResult) {
                ResponseHelper::error('User not found.', 404);
                return;
            }
            $user = $userResult['data'];
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

    public function deleteCard($cardId): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            $deleted = $this->cardService->deleteCard($decodedUser->id, $cardId);
            if (!$deleted['success']) {
                ResponseHelper::error("Couldn't delete card: Unautorized, Card not found or Card is listed.", 400);
                return;
            }

            ResponseHelper::success(null, 'Card deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('An error occurred while deleting the card: ' . $e->getMessage(), 500);
        }
    }
    
    public function adminDeleteCard($id): void {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();
            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('Access denied. Admins only.', 403);
                return;
            }
            $this->cardService->deleteCard($id);

            ResponseHelper::success(null, 'Card deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to delete card: ' . $e->getMessage(), 400);
        }
    }
}