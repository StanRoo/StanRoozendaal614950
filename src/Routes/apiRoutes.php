<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\CardController;
use App\Controllers\TransactionController;

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$authController = $authController;
$userController = $userController;
$cardController = $cardController;
$transactionController = $transactionController;
$authMiddleware = $authMiddleware;

switch (true) {
    // ------------User Routes---------------

    // Get user by user_id
    case $requestUri === '/api/user' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();     
        $userController->getUser($decodedUser->id); 
        break;

    // Get all users (Admin only)
    case $requestUri === '/api/users' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->getAllUsers($decodedUser);
        break;

    // Get a user's inventory
    case $requestUri === '/api/user/inventory' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $transactionController->getUserInventory($decodedUser->id);
        break;

    // Get current user balance and last claim
    case $requestUri === '/api/user/balance' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->getUserBalance($decodedUser->id);
        break;

    // Update user by ID (Admin)
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $userId = $matches[1];
        $userController->updateUser($userId);
        break;

    // Update user profile
    case $requestUri === '/api/user' && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->updateUser($decodedUser->id);
        break;

    // Update profile picture
    case $requestUri === '/api/user/upload-profile-picture' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->updateProfilePicture($decodedUser->id);
        break;

    // Login
    case $requestUri === '/api/login' && $requestMethod === 'POST':
        $authController->login();
        break;

    // Register new user
    case $requestUri === '/api/register' && $requestMethod === 'POST':
        $authController->register();
        break;

    // Claim daily reward
    case $requestUri === '/api/user/claim-daily' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->claimDailyCuboCoins($decodedUser->id);
        break;

    // Delete user (Admin only)
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $userId = $matches[1];
        $userController->deleteUser($userId);
        break;

    // --------------Card Routes (Marketplace)--------------

    // Create a new Pokémon card
    case $requestUri === '/api/cards' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $cardController->createCard($decodedUser->id);
        break;

    // Get all available Pokémon cards
    case $requestUri === '/api/cards' && $requestMethod === 'GET':
        $cardController->getAllCards();
        break;

    // Get all cards created by the user
    case $requestUri === '/api/cards/user' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $cardController->getUserCards($decodedUser->id);
        break;

    // Get a specific card by ID
    case preg_match('/\/api\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $cardId = $matches[1];
        $cardController->getCardById($cardId);
        break;

    // Delete card
    case preg_match('/\/api\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $cardId = $matches[1];
        $cardController->deleteCard($cardId);
        break;

    // -------------Transaction Routes-----------------

    // Buy a Pokémon card
    case $requestUri === '/api/cards/buy' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $transactionController->buyCard($decodedUser->id);
        break;

    // -----------Default 404 Response------------
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}