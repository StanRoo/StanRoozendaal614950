<?php

namespace App\Services;

use App\Repositories\CardRepository;
use App\Models\CardModel;
use App\Utils\Validator;
use App\Utils\ErrorHandler;
use App\Utils\ImageUploader;
use Exception;

class CardService {
    private $cardRepository;

    public function __construct(CardRepository $cardRepository) {
        $this->cardRepository = $cardRepository;
    }

    public function getCardById($id) {
        return $this->cardRepository->getCardById($id);
    }

    public function getUserCards($userId) {
        return $this->cardRepository->getUserCards($userId);
    }

    public function createCard($userId, $data, $image) {
        if (empty($data['name']) || empty($data['type']) || empty($data['rarity'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }
        $imagePath = ImageUploader::upload($image, 'cards');
        if (!$imagePath) {
            return ['success' => false, 'message' => 'Failed to upload image'];
        }
        $cardData = [
            'user_id' => $userId,
            'name' => $data['name'],
            'type' => $data['type'],
            'hp' => $data['hp'] ?? 0,
            'attack' => $data['attack'] ?? 0,
            'defense' => $data['defense'] ?? 0,
            'speed' => $data['speed'] ?? 0,
            'image_url' => $imagePath,
            'rarity' => $data['rarity'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $createdCard = $this->cardRepository->create($cardData);
        if ($createdCard) {
            $cardModel = new CardModel($cardData);
            return ['success' => true, 'message' => 'Card created successfully', 'data' => $cardModel->toArray()];
        }
        return ['success' => false, 'message' => 'Failed to create card'];
    }

    public function deleteCard($userId, $cardId) {
        $card = $this->cardRepository->getCardById($cardId);
        if (!$card) {
            return ErrorHandler::respondWithError(404, "Card not found.");
        }
        if ($card->user_id !== $userId) {
            return ErrorHandler::respondWithError(403, "Unauthorized: You don't own this card.");
        }
        return $this->cardRepository->delete($cardId);
    }

    public function updateCardOwner($cardId, $newOwnerId): bool {
        return $this->cardRepository->updateCardOwner($cardId, $newOwnerId);
    }
}