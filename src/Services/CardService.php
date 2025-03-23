<?php

namespace App\Services;

use App\Repositories\CardRepository;
use App\Models\CardModel;
use App\Utils\Validator;
use App\Utils\ErrorHandler;
use Exception;

class CardService {
    private $cardRepository;

    public function __construct(CardRepository $cardRepository) {
        $this->cardRepository = $cardRepository;
    }

    public function getCardById($id) {
        return $this->cardRepository->getById($id);
    }

    public function getAllCards($userId = null) {
        return $this->cardRepository->getAll($userId);
    }

    public function createCard($userId, $name, $rarity, $price, $image_url) {
        if (Validator::validateCardName($name) !== true) {
            return ErrorHandler::respondWithError(400, "Invalid card name.");
        }
        if (Validator::validatePrice($price) !== true) {
            return ErrorHandler::respondWithError(400, "Invalid price.");
        }

        $card = new CardModel([
            'user_id' => $userId,
            'name' => $name,
            'rarity' => $rarity,
            'price' => $price,
            'image_url' => $image_url
        ]);
        return $this->cardRepository->create($card);
    }

    public function deleteCard($userId, $cardId) {
        $card = $this->cardRepository->getById($cardId);
        if (!$card) {
            return ErrorHandler::respondWithError(404, "Card not found.");
        }
        if ($card->user_id !== $userId) {
            return ErrorHandler::respondWithError(403, "Unauthorized: You don't own this card.");
        }
        return $this->cardRepository->delete($cardId);
    }
}