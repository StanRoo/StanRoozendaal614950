<?php

namespace App\Models;

class UserModel {
    public ?int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $role;
    public string $status;
    public ?string $profile_picture_url;
    public ?string $bio;
    public ?string $created_at;
    public ?string $updated_at;
    public ?string $last_login;
    public float $balance;
    public ?string $last_daily_claim;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'user'; 
        $this->status = $data['status'] ?? 'active'; 
        $this->profile_picture_url = $data['profile_picture_url'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->last_login = $data['last_login'] ?? null;
        $this->balance = $data['balance'] ?? 1000.00;
        $this->last_daily_claim = $data['last_daily_claim'] ?? null;
    }

    public function toArray() {
        return get_object_vars($this);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getProfilePictureUrl(): ?string {
        return $this->profile_picture_url;
    }

    public function getBio(): ?string {
        return $this->bio;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    public function getLastLogin(): ?string {
        return $this->last_login;
    }

    public function getBalance(): float {
        return $this->balance;
    }

    public function getLastClaimed(): ?string {
        return $this->last_daily_claim;
    }
}