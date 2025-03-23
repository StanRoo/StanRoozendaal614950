<?php

namespace App\Repositories;

use App\Models\TransactionModel;
use PDO;

class TransactionRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getTransactionById($id): ?TransactionModel {
        $stmt = $this->pdo->prepare("SELECT id, buyer_id, seller_id, card_id, price, transaction_date, status FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        $transactionData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $transactionData ? new TransactionModel($transactionData) : null;
    }

    public function getAllTransactions($userId = null): array {
        $sql = "SELECT id, buyer_id, seller_id, card_id, price, transaction_date, status FROM transactions";
        if ($userId) {
            $sql .= " WHERE buyer_id = ?";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($userId ? [$userId] : []);
        return array_map(fn($row) => new TransactionModel($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(TransactionModel $transaction): ?TransactionModel {
        $stmt = $this->pdo->prepare("INSERT INTO transactions (buyer_id, card_id, price, transaction_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$transaction->buyer_id, $transaction->card_id, $transaction->price, $transaction->transaction_date]);
        return $this->getTransactionById($this->pdo->lastInsertId());
    }
}