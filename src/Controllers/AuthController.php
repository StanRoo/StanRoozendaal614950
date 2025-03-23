<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Utils\ErrorHandler;

class AuthController {
    private $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function login() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (empty($data['username']) || empty($data['password'])) {
                ErrorHandler::respondWithError(400, "Username and password are required.");
            }

            $username = $data['username'];
            $password = $data['password'];

            $result = $this->authService->login($username, $password);

            if (isset($result['error'])) {
                ErrorHandler::respondWithError(401, $result['message']);
            } else {
                echo json_encode($result);
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleException($e);
        }
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['username'], $data['email'], $data['password'])) {
                ErrorHandler::respondWithError(400, "Missing required fields");
            }

            $result = $this->authService->register($data);

            if (isset($result['error'])) {
                ErrorHandler::respondWithError(400, $result['message']);
            } else {
                echo json_encode(["message" => "Account created successfully!"]);
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleException($e);
        }
    }
}