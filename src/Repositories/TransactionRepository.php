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

    public function getAllTransactions(int $limit, int $offset, array $filters = []): array
    {
        $sql = "
            SELECT t.*, 
                b.username AS buyer_username, 
                s.username AS seller_username, 
                c.name AS card_name, 
                c.image_url AS card_image_url,
                c.rarity AS card_rarity
            FROM transactions t
            JOIN users b ON t.buyer_id = b.id
            JOIN users s ON t.seller_id = s.id
            JOIN cards c ON t.card_id = c.id
        ";
        $where = [];
        $params = [];
        if (!empty($filters['buyer'])) {
            $where[] = 'b.username LIKE :buyer';
            $params[':buyer'] = '%' . $filters['buyer'] . '%';
        }
        if (!empty($filters['seller'])) {
            $where[] = 's.username LIKE :seller';
            $params[':seller'] = '%' . $filters['seller'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        if (!empty($filters['date'])) {
            $direction = strtoupper($filters['date']) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY t.transaction_date $direction";
        } else {
            $sql .= " ORDER BY t.transaction_date DESC";
        }
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $transactionsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($t) => new \App\Models\TransactionModel($t), $transactionsData);
    }

    public function getTransactionsCount(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*) 
            FROM transactions t
            JOIN users b ON t.buyer_id = b.id
            JOIN users s ON t.seller_id = s.id
            JOIN cards c ON t.card_id = c.id
        ";
        $where = [];
        $params = [];
        if (!empty($filters['buyer'])) {
            $where[] = 'b.username LIKE :buyer';
            $params[':buyer'] = '%' . $filters['buyer'] . '%';
        }
        if (!empty($filters['seller'])) {
            $where[] = 's.username LIKE :seller';
            $params[':seller'] = '%' . $filters['seller'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 't.status = :status';
            $params[':status'] = $filters['status'];
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function createTransaction(TransactionModel $transaction): bool {
        $stmt = $this->pdo->prepare("INSERT INTO transactions (buyer_id, seller_id, card_id, price, transaction_date, status) VALUES (:buyer_id, :seller_id, :card_id, :price, :transaction_date, :status)");
        $stmt->bindValue(':buyer_id', $transaction->getBuyerId(), PDO::PARAM_INT);
        $stmt->bindValue(':seller_id', $transaction->getSellerId(), PDO::PARAM_INT);
        $stmt->bindValue(':card_id', $transaction->getCardId(), PDO::PARAM_INT);
        $stmt->bindValue(':price', $transaction->getPrice());
        $stmt->bindValue(':transaction_date', $transaction->getTransactionDate());
        $stmt->bindValue(':status', $transaction->getStatus());
        return $stmt->execute();
    }

    public function deleteTransaction($transactionId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM transactions WHERE id = :id");
        $stmt->bindParam(':id', $transactionId, \PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            throw new \Exception("Failed to delete transaction");
        }
    }
}