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
}