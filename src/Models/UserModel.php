<?php

namespace App\Models;

class UserModel {
    public ?int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $role;
    public string $status;
    public ?string $profilePictureUrl;
    public ?string $bio;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $lastLogin;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'user'; 
        $this->status = $data['status'] ?? 'active'; 
        $this->profilePictureUrl = $data['profile_picture_url'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->lastLogin = $data['last_login'] ?? null;
    }

    public function getId(): int {
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

    public function getProfilePictureUrl(): string {
        return $this->profile_picture_url;
    }

    public function getBio(): string {
        return $this->bio;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getRole(): string {
        return $this->role;
    }
}