<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$userController = new UserController();
$authMiddleware = new AuthMiddleware();

switch (true) {
    // GET
    // Get single user by user_id (authenticated user)
    case $requestUri === '/api/user' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();     
        $userController->getUser($decodedUser->id); 
        break;

    // Get all users (Admin only)
    case $requestUri === '/api/users' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->getAllUsers();
        break;

    // PUT
    // Update user by ID (Admin)
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $userId = $matches[1];
        $userController->updateUser($userId);
        break;
    
    // Update user profile (User can only update their own profile)
    case $requestUri === '/api/user' && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->updateUser($decodedUser->id);
        break;

    // POST
    // Register new user
    case $requestUri === '/api/register' && $requestMethod === 'POST':
        $authController->register();
        break;

    // Login
    case $requestUri === '/api/login' && $requestMethod === 'POST':
        $authController->login();
        break;
    
    // Update profile picture
    case $requestUri === '/api/user/upload-profile-picture' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->updateProfilePicture($decodedUser->id);
        break;

    // DELETE
    // Delete user (Admin only)
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $userId = $matches[1];
        $userController->deleteUser($userId);
        break;

    // Default response (if not existing route)
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}