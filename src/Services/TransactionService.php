<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\CardRepository;
use App\Models\TransactionModel;
use App\Utils\ErrorHandler;
use Exception;

class TransactionService {
    private $transactionRepository;
    private $cardRepository;

    public function __construct(TransactionRepository $transactionRepository, CardRepository $cardRepository) {
        $this->transactionRepository = $transactionRepository;
        $this->cardRepository = $cardRepository;
    }

    public function getTransactionById($id) {
        return $this->transactionRepository->getById($id);
    }

    public function getAllTransactions($userId = null) {
        return $this->transactionRepository->getAll($userId);
    }

    public function purchaseCard($buyerId, $cardId) {
        $card = $this->cardRepository->getById($cardId);

        if (!$card) {
            return ErrorHandler::respondWithError(404, "Card not found.");
        }
        if ($card->user_id == $buyerId) {
            return ErrorHandler::respondWithError(400, "You cannot buy your own card.");
        }

        $transaction = new TransactionModel([
            'buyer_id' => $buyerId,
            'card_id' => $cardId,
            'price' => $card->price,
            'transaction_date' => date('Y-m-d H:i:s')
        ]);

        return $this->transactionRepository->create($transaction);
    }
}