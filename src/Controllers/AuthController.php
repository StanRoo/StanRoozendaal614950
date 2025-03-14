<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Utils\Response;

class AuthController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['username']) || empty($data['password'])) {
            Response::error(400, "Username and password are required.");
            return;
        }

        $username = $data['username'];
        $password = $data['password'];

        $result = $this->authService->login($username, $password);

        if (isset($result['error'])) {
            Response::error(401, $result['message']);
        } else {
            echo json_encode($result);
        }
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['username'], $data['email'], $data['password'])) {
            Response::error(400, "Missing required fields");
            return;
        }

        $result = $this->authService->register($data);

        if (isset($result['error'])) {
            Response::error(400, $result['message']);
        } else {
            echo json_encode(["message" => "Account created successfully!"]);
        }
    }
}