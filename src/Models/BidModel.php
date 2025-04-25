<?php

namespace App\Models;

class BidModel {
    public ?int $id;
    public int $listing_id;
    public int $bidder_id;
    public float $bid_amount;
    public ?string $created_at;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->listing_id = $data['listing_id'] ?? 0;
        $this->bidder_id = $data['bidder_id'] ?? 0;
        $this->bid_amount = $data['bid_amount'] ?? 0.00;
        $this->created_at = $data['bid_time'] ?? null;
    }

    public function toArray(): array {
        return get_object_vars($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getListingId(): int {
        return $this->listing_id;
    }

    public function getBidderId(): int {
        return $this->bidder_id;
    }

    public function getBidAmount(): float {
        return $this->bid_amount;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }
}