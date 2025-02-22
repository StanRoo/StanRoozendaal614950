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
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->role = $data['role'] ?? 'user'; 
        $this->status = $data['status'] ?? 'active'; 
        $this->profilePictureUrl = $data['profile_picture_url'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->lastLogin = $data['last_login'] ?? null;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'profile_picture_url' => $this->profilePictureUrl,
            'bio' => $this->bio,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_login' => $this->lastLogin
        ];
    }
}