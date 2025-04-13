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

    public function logTransaction($buyerId, $sellerId, $cardId, $price): bool {
        $transaction = new TransactionModel([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'card_id' => $cardId,
            'price' => $price,
            'transaction_date' => date('Y-m-d H:i:s'),
            'status' => 'Completed',
        ]);
        return $this->transactionRepository->createTransaction($transaction);
    }
}