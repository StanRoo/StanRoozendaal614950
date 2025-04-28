<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;
use App\Utils\ResponseHelper;

class TransactionController {
    private $transactionService;
    private $authMiddleware;

    public function __construct(TransactionService $transactionService, AuthMiddleware $authMiddleware) {
        $this->transactionService = $transactionService;
        $this->authMiddleware = $authMiddleware;
    }

    public function getAllTransactions(): void {
        try {
            $transactions = $this->transactionService->getAllTransactions();
            
            $formattedTransactions = array_map(function($transaction) {
                return [
                    'id' => $transaction->getId(),
                    'buyer_username' => $transaction->getBuyerUsername(),
                    'seller_username' => $transaction->getSellerUsername(),
                    'card_name' => $transaction->getCardName(),
                    'card_image_url' => $transaction->getCardImageUrl(),
                    'card_rarity' => $transaction->getCardRarity(),
                    'price' => $transaction->getPrice(),
                    'transaction_date' => $transaction->getTransactionDate(),
                    'status' => $transaction->getStatus()
                ];
            }, $transactions);

            ResponseHelper::success(['transactions' => $formattedTransactions], 'Transactions fetched successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to fetch transactions: ' . $e->getMessage(), 500);
        }
    }

    public function deleteTransaction($user, $transactionId): void {
        try {
            if ($user->role !== 'admin') {
                ResponseHelper::error('Forbidden: Admin access required.', 403);
                return;
            }

            $this->transactionService->deleteTransaction($transactionId);

            ResponseHelper::success(null, 'Transaction deleted successfully.');
        } catch (\Throwable $e) {
            ResponseHelper::error('Failed to delete transaction: ' . $e->getMessage(), 500);
        }
    }
}