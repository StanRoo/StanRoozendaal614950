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

    public function getAllTransactions($decodedUser, int $page = 1, int $limit = 10, array $filters = []): array
    {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return [
                'success' => false,
                'message' => 'Unauthorized: Admin access required.',
                'data' => null
            ];
        }

        $offset = ($page - 1) * $limit;

        $total = $this->transactionRepository->getTransactionsCount($filters);
        $transactions = $this->transactionRepository->getAllTransactions($limit, $offset, $filters);

        return [
            'success' => true,
            'message' => 'Transactions retrieved successfully.',
            'data' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];
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