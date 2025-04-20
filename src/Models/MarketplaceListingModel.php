<?php

namespace App\Models;

class MarketplaceListingModel {
    public $id;
    public $card_id;
    public $seller_id;
    public $price;
    public $listed_at;
    public $status;
    public $min_bid_price;
    public $expires_at;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->card_id = $data['card_id'];
        $this->seller_id = $data['seller_id'];
        $this->price = $data['price'];
        $this->listed_at = $data['listed_at'] ?? date("Y-m-d H:i:s");
        $this->status = $data['status'] ?? 'active';
        $this->min_bid_price = $data['min_bid_price'] ?? null;
        $this->expires_at = $data['expires_at'] ?? null;
    }

    public function toArray(): array {
        return get_object_vars($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCardId(): int {
        return $this->card_id;
    }

    public function getSellerId(): int {
        return $this->seller_id;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getListedAt(): string {
        return $this->listed_at;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getMinBidPrice(): ?float {
        return $this->min_bid_price;
    }

    public function getExpiresAt(): ?string {
        return $this->expires_at;
    }
}