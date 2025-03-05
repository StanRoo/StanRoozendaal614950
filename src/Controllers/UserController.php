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

    public function getAllUsers() {
        $decodedUser = AuthMiddleware::verifyToken();

        error_log("🔍 Decoded JWT User: " . print_r($decodedUser, true));
    
        if (!isset($decodedUser['user']->role) || $decodedUser['user']->role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
            exit;
        }
    
        $users = $this->userRepository->getAllUsers();
        echo json_encode(["users" => $users]);
    }

    public function updateUser($userId) {
        $decodedUser = AuthMiddleware::verifyToken();
    
        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true);
    
        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
            return;
        }
    
        if ($decodedUser['user']->role === 'admin') {
            if (isset($data['role']) || isset($data['status'])) {
                $this->userRepository->updateUser($userId, $data);
                echo json_encode(["success" => true, "message" => "User updated successfully"]);
                return;
            }
        } elseif ($decodedUser['user']->id == $userId) {
            unset($data['role']);
            unset($data['status']);
    
            $this->userRepository->updateUser($userId, $data);
            echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
            return;
        }
    
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized"]);
    }

    public function updateProfilePicture($userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['profile_picture_url'])) { 
            $success = $this->userRepository->updateProfilePicture($userId, $data['profile_picture_url']);

            if ($success) {
                echo json_encode(["success" => true, "profile_picture_url" => $data['profile_picture_url']]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update profile picture"]);
            }
            return;
        }

        if (isset($_FILES['profile_picture'])) {
            $targetDir = "uploads/";
            $fileName = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            $allowedTypes = ["jpg", "jpeg", "png"];
            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["error" => "Only JPG, JPEG, and PNG files are allowed"]);
                return;
            }

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                $success = $this->userRepository->updateProfilePicture($userId, $targetFilePath);
                if ($success) {
                    echo json_encode(["success" => true, "profile_picture_url" => $targetFilePath]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to update profile picture"]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to upload file"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "No file uploaded"]);
        }
    }

    public function deleteUser($id) {
        $decodedUser = AuthMiddleware::verifyToken();
    
        if (!isset($decodedUser['user']->role) || $decodedUser['user']->role !== 'admin') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
            exit;
        }
    
        $this->userRepository->deleteUser($id);
        echo json_encode(["message" => "User deleted successfully!"]);
    }
}