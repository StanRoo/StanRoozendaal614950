<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware; 

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$userController = new UserController();

switch (true) {
    case $requestUri === '/api/register' && $requestMethod === 'POST':
        $authController->register();
        break;

    case $requestUri === '/api/login' && $requestMethod === 'POST':
        $authController->login();
        break;

    case $requestUri === '/api/user' && $requestMethod === 'GET':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->getUser($decodedUser['user_id']);
        break;

    case $requestUri === '/api/user' && $requestMethod === 'PUT':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->updateUser($decodedUser['user_id']);
        break;

    case $requestUri === '/api/user/upload-profile-picture' && $requestMethod === 'POST':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->updateProfilePicture($decodedUser['user_id']);
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}