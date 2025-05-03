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

    public function getAllTransactions(): void
    {
        try {
            $decodedUser = $this->authMiddleware->verifyToken();

            if ($decodedUser->role !== 'admin') {
                ResponseHelper::error('Access denied. Admins only.', 403);
                return;
            }

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            $filters = [
                'buyer' => $_GET['buyer'] ?? null,
                'seller' => $_GET['seller'] ?? null,
                'status' => $_GET['status'] ?? null,
                'date' => $_GET['date'] ?? null
            ];

            $result = $this->transactionService->getAllTransactions($decodedUser, $page, $limit, $filters);

            if (!$result['success']) {
                ResponseHelper::error($result['message'], 500);
                return;
            }

            ResponseHelper::success([
                'transactions' => $result['data'],
                'pagination' => $result['pagination']
            ], 'Transactions fetched successfully.');
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