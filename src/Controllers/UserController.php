<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class UserController {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function getUser($userId) {
        $user = $this->userRepository->getUserById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            return;
        }

        echo json_encode(["success" => true, "user" => $user]);
    }

    public function updateUser($userId) {
        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
            return;
        }

        $success = $this->userRepository->updateUser($userId, $data);

        if ($success) {
            echo json_encode(["success" => true, "message" => "User updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update user"]);
        }
    }

    public function updateProfilePicture($userId, $profilePictureUrl) {
        if (!$userId) {
            http_response_code(403);
            echo json_encode(["error" => "Unauthorized"]);
            return;
        }

        $success = $this->userRepository->updateProfilePicture($userId, $profilePictureUrl);

        if ($success) {
            echo json_encode(["success" => true, "message" => "Profile picture updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database update failed"]);
        }
    }
}