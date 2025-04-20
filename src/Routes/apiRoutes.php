<?php

use App\Middleware\AuthMiddleware;
use App\Utils\ErrorHandler;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\CardController;
use App\Controllers\TransactionController;
use App\Controllers\MarketplaceController;
use App\Controllers\BidController;

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$authController = $authController;
$userController = $userController;
$cardController = $cardController;
$transactionController = $transactionController;
$marketplaceController = $marketplaceController;
$bidController = $bidController;
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

    // -----------Marketplace Routes--------------
    
    // Get all cards on the marketplace (except user listed cards)
    case $requestUri === '/api/marketplace/list' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->getMarketplaceCards($decodedUser->id);
        break;

    // Get user's marketplace listings
    case $requestUri === '/api/marketplace/userListings' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->getUserListings($decodedUser->id);
        break;

    // Get listed card info
    case preg_match('/\/api\/marketplace\/card\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $cardId = $matches[1];
        $marketplaceController->getMarketplaceCard($cardId);
        break;

    // List a card on the marketplace
    case $requestUri === '/api/marketplace/list' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->listCard($decodedUser->id);
        break;

    // Get a specific card's marketplace listing details
    case preg_match('/\/api\/marketplace\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $cardId = $matches[1];
        $marketplaceController->getMarketplaceCardDetails($cardId);
        break;

    // Update card price in the marketplace
    case preg_match('/\/api\/marketplace\/(\d+)\/price/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $cardId = $matches[1];
        $marketplaceController->updateCardPrice($decodedUser->id, $cardId);
        break;

    // Finalize expired listings
    case $requestUri === '/api/marketplace/finalizeExpired' && $requestMethod === 'POST':
        $marketplaceController->finalizeExpiredListings();
        break;

    // ---------------- Bid Routes --------------------

    // Place a bid on a listing
    case $requestUri === '/api/bid/place' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $bidController->placeBid($decodedUser->id);
        break;

    // Get bids for a specific listing
    case $requestUri === '/api/bid/listing' && $requestMethod === 'GET':
        $bidController->getBidsForListing();
        break;

    // Get current user's bids (optional)
    case $requestUri === '/api/bid/my' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $_SESSION['user_id'] = $decodedUser->id;
        $bidController->getMyBids();
        break;

    // Get highest bid for a specific listing
    case preg_match('/\/api\/marketplace\/highestBid\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $listingId = $matches[1];
        $marketplaceController->getHighestBid($listingId);
        break;

    // Buy a Pokémon card
    case $requestUri === '/api/marketplace/buyNow' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $requestBody = json_decode(file_get_contents("php://input"), true);
        if (!isset($requestBody['listing_id'])) {
            ErrorHandler::respondWithError(400, "Listing ID is missing.");
            return;
        }
        $listingId = $requestBody['listing_id'];
        $buyerId = $decodedUser->id;
        $marketplaceController->buyCard($listingId, $buyerId);
        break;

    // -----------Default 404 Response------------
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}