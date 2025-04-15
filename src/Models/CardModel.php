<?php

namespace App\Models;

class CardModel {
    public $id;
    public $user_id;
    public $owner_id;
    public $name;
    public $type;
    public $hp;
    public $attack;
    public $defense;
    public $speed;
    public $image_url;
    public $rarity;
    public $is_listed;
    public $created_at;
    public $updated_at;

    public function __construct($data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'];
        $this->owner_id = $data['owner_id'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->hp = $data['hp'];
        $this->attack = $data['attack'];
        $this->defense = $data['defense'];
        $this->speed = $data['speed'];
        $this->image_url = $data['image_url'];
        $this->rarity = $data['rarity'];
        $this->is_listed = $data['is_listed'] ?? 0;
        $this->created_at = $data['created_at'] ?? date("Y-m-d H:i:s");
        $this->updated_at = $data['updated_at'] ?? date("Y-m-d H:i:s");
    }

    public function toArray(): array {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "owner_id" => $this->owner_id,
            "name" => $this->name,
            "type" => $this->type,
            "hp" => $this->hp,
            "attack" => $this->attack,
            "defense" => $this->defense,
            "speed" => $this->speed,
            "image_url" => $this->image_url,
            "rarity" => $this->rarity,
            "is_listed" => $this->is_listed,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getOwnerId(): int {
        return $this->owner_id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getHp(): int {
        return $this->hp;
    }

    public function getAttack(): int {
        return $this->attack;
    }

    public function getDefense(): int {
        return $this->defense;
    }

    public function getSpeed(): int {
        return $this->speed;
    }

    public function getImageUrl(): ?string {
        return $this->image_url;
    }

    public function getRarity(): string {
        return $this->rarity;
    }

    public function isListed(): bool {
        return $this->is_listed;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }
}