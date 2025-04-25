<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;

class TransactionController {
    private $transactionService;
    private $authMiddleware;

    public function __construct(TransactionService $transactionService, AuthMiddleware $authMiddleware) {
        $this->transactionService = $transactionService;
        $this->authMiddleware = $authMiddleware;
    }

    public function getAllTransactions() {
        try {
            $transactions = $this->transactionService->getAllTransactions();      
            $formattedTransactions = [];
            foreach ($transactions as $transaction) {
                $formattedTransactions[] = [
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
            }
            echo json_encode(['transactions' => $formattedTransactions]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, 'Failed to fetch transactions');
        }
    }

    public function deleteTransaction($user, $transactionId)
    {
        try {
            if ($user->role !== 'admin') {
                ErrorHandler::respondWithError(403, 'Forbidden');
                return;
            }
            $this->transactionService->deleteTransaction($transactionId);
            echo json_encode(["message" => "Transaction deleted successfully"]);
        } catch (\Exception $e) {
            ErrorHandler::respondWithError(500, $e->getMessage());
        }
    }
}