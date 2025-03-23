<?php

namespace App\Models;

class CardModel {
    public $id;
    public $user_id;
    public $name;
    public $rarity;
    public $price;
    public $image_url;
    public $created_at;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'];
        $this->name = $data['name'];
        $this->rarity = $data['rarity'];
        $this->price = $data['price'];
        $this->image_url = $data['image_url'];
        $this->created_at = $data['created_at'] ?? null;
    }

    public function toArray() {
        return get_object_vars($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRarity(): string {
        return $this->rarity;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getImageUrl(): ?string {
        return $this->image_url;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }
}