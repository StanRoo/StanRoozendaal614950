<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\CardRepository;
use App\Models\TransactionModel;

class TransactionService
{
    private TransactionRepository $transactionRepository;
    private CardRepository $cardRepository;

    public function __construct(TransactionRepository $transactionRepository, CardRepository $cardRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->cardRepository = $cardRepository;
    }

    public function getTransactionById(int $id): ?TransactionModel
    {
        return $this->transactionRepository->getById($id);
    }

    public function getAllTransactions(): array
    {
        return $this->transactionRepository->getAllTransactions();
    }

    public function logTransaction(int $buyerId, int $sellerId, int $cardId, float $price): bool
    {
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

    public function deleteTransaction(int $transactionId): void
    {
        $this->transactionRepository->deleteTransaction($transactionId);
    }
}