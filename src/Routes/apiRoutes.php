<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware; 

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$userController = new UserController();

switch (true) {
    // GET
    //Get single user by user_id
    case $requestUri === '/api/user' && $requestMethod === 'GET':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->getUser($decodedUser['user_id']);
        break;

    //Get all users
    case $requestUri === '/api/users' && $requestMethod === 'GET':
        $decodedUser = AuthMiddleware::verifyToken();
        $userController->getAllUsers();
        break;

    // PUT
    //Update users (admin)    
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userId = $matches[1];
        $userController->updateUser($userId);
        break;
    
    //Update user (user)    
    case $requestUri === '/api/user' && $requestMethod === 'PUT':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->updateUser($decodedUser['user_id']);
        break;

    // POST
    //Register
    case $requestUri === '/api/register' && $requestMethod === 'POST':
        $authController->register();
        break;

    //Login
    case $requestUri === '/api/login' && $requestMethod === 'POST':
        $authController->login();
        break;
    
    //Update profile picture    
    case $requestUri === '/api/user/upload-profile-picture' && $requestMethod === 'POST':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userController->updateProfilePicture($decodedUser['user_id']);
        break;

    // DELETE
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = AuthMiddleware::verifyToken(); 
        $userId = $matches[1];
        $userController->deleteUser($userId);
        break;

    //Default response (if not existing route)    
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}