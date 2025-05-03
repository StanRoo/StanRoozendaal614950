<?php

namespace App\Services;

use App\Repositories\CardRepository;
use App\Models\CardModel;
use App\Utils\ImageUploader;

class CardService {
    private CardRepository $cardRepository;

    public function __construct(CardRepository $cardRepository) {
        $this->cardRepository = $cardRepository;
    }

    public function getCardById($id) {
        return $this->cardRepository->getCardById($id);
    }

    public function getUserCards(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        try {
            $total = $this->cardRepository->getUserCardsCount($userId, $filters);
            $cards = $this->cardRepository->getUserCards($userId, $offset, $limit, $filters);

            return [
                'success' => true,
                'message' => 'User cards retrieved successfully.',
                'data' => $cards,
                'pagination' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'total' => $total,
                    'hasMore' => ($offset + $limit) < $total
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching cards: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function createCard($userId, $data, $image): array {
        if (empty($data['name']) || empty($data['type']) || empty($data['rarity'])) {
            return ['success' => false, 'message' => 'Missing required fields.'];
        }

        $imagePath = ImageUploader::upload($image, 'cards');
        if (!$imagePath) {
            return ['success' => false, 'message' => 'Failed to upload image.'];
        }

        $cardData = [
            'user_id' => $userId,
            'owner_id' => $userId,
            'name' => $data['name'],
            'type' => $data['type'],
            'hp' => $data['hp'] ?? 0,
            'attack' => $data['attack'] ?? 0,
            'defense' => $data['defense'] ?? 0,
            'speed' => $data['speed'] ?? 0,
            'image_url' => $imagePath,
            'rarity' => $data['rarity'],
            'is_listed' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $createdCard = $this->cardRepository->create($cardData);

        if ($createdCard) {
            $cardModel = new CardModel($cardData);
            return [
                'success' => true,
                'data' => $cardModel->toArray(),
                'message' => 'Card created successfully.'
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to create card.'];
        }
    }

    public function deleteCard($userId, $cardId): void {
        $card = $this->cardRepository->getCardById($cardId);

        if (!$card || $card->user_id !== $userId || $card->is_listed) {
            \App\Utils\ResponseHelper::error('Card not found or cannot be deleted', 403);
        }

        $this->cardRepository->delete($cardId);
        \App\Utils\ResponseHelper::success(null, 'Card deleted successfully');
    }

    public function updateCardOwner($cardId, $newOwnerId) {
        return $this->cardRepository->updateCardOwner($cardId, $newOwnerId);
    }

    public function setCardListingStatus($cardId, bool $status) {
        return $this->cardRepository->setCardListedStatus($cardId, $status);
    }

    public function getAllCards($decodedUser, int $page = 1, int $limit = 10, array $filters = []): array
    {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return ['success' => false, 'message' => 'Unauthorized: Admin access required.', 'data' => null];
        }

        $offset = ($page - 1) * $limit;
        $total = $this->cardRepository->getCardsCount($filters);
        $cards = $this->cardRepository->getAllCards($limit, $offset, $filters);

        return [
            'success' => true,
            'message' => 'Cards retrieved successfully.',
            'data' => $cards,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];
    }

    public function updateCard(int $id, array $data): void {
        $this->cardRepository->updateCard($id, $data);
    }

    public function adminDeleteCard(int $id): void {
        $this->cardRepository->deleteCard($id);
    }
}