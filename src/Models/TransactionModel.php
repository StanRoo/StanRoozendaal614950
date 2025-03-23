<?php

namespace App\Models;

class TransactionModel {
    public $id;
    public $buyer_id;
    public $seller_id;
    public $card_id;
    public $price;
    public $transaction_date;
    public $status;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->buyer_id = $data['buyer_id'];
        $this->seller_id = $data['seller_id'];
        $this->card_id = $data['card_id'];
        $this->price = $data['price'];
        $this->transaction_date = $data['transaction_date'] ?? date('Y-m-d H:i:s');
        $this->status = $data['status'] ?? 'Completed';
    }

    public function toArray() {
        return get_object_vars($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getBuyerId(): int {
        return $this->buyer_id;
    }

    public function getSellerId(): int {
        return $this->seller_id;
    }

    public function getCardId(): int {
        return $this->card_id;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getTransactionDate(): string {
        return $this->transaction_date;
    }

    public function getStatus(): string {
        return $this->status;
    }
}