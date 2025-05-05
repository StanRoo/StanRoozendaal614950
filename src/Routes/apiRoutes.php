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
        $userController->getUserById($decodedUser->id); 
        break;

    // Get user by username
    case $requestUri === '/api/user/username' && $requestMethod === 'GET':     
        $userController->checkUsername(); 
        break;

    // Get user by email
    case $requestUri === '/api/user/email' && $requestMethod === 'GET':     
        $userController->checkEmail(); 
        break;

    // Get current user balance and last claim
    case $requestUri === '/api/user/balance' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->getUserBalance($decodedUser->id);
        break;

    // Get all cards owned by the user
    case $requestUri === '/api/user/cards' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $cardController->getUserCards($decodedUser->id);
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
    case $requestUri === '/api/user/profile-picture' && $requestMethod === 'POST':
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

    // Send password reset link
    case $requestUri === '/api/forgot-password' && $requestMethod === 'POST':
        $authController->sendPasswordResetLink();
        break;
    
    // Reset password   
    case $requestUri === '/api/reset-password' && $requestMethod === 'POST':
        $authController->resetPassword();
        break;

    // --------------Card Routes (Marketplace)--------------

    // Get all available Pokémon cards
    case $requestUri === '/api/cards' && $requestMethod === 'GET':
        $cardController->getAllCards();
        break;

    // Get a specific card by ID
    case preg_match('/\/api\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $cardId = $matches[1];
        $cardController->getCardById($cardId);
        break;

    // Create a new Pokémon card
    case $requestUri === '/api/cards' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $cardController->createCard($decodedUser->id);
        break;

    // Delete card
    case preg_match('/\/api\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $cardId = $matches[1];
        $cardController->deleteCard($cardId);
        break;

    // -----------Marketplace Routes--------------
    
    // Get all cards on the marketplace (except user listed cards)
    case $requestUri === '/api/marketplace' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->getMarketplaceCards($decodedUser->id);
        break;

    // Get user's marketplace listings
    case $requestUri === '/api/marketplace/user-listings' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->getUserListings($decodedUser->id);
        break;

    // Get listed card info
    case preg_match('/\/api\/marketplace\/card\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $cardId = $matches[1];
        $marketplaceController->getMarketplaceCard($cardId);
        break;

    // Update card price in the marketplace
    case preg_match('/\/api\/marketplace\/(\d+)\/price/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $cardId = $matches[1];
        $marketplaceController->updateCardPrice($decodedUser->id, $cardId);
        break;

    // List a card on the marketplace
    case $requestUri === '/api/marketplace' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->listCard($decodedUser->id);
        break;

    // Finalize expired listings
    case $requestUri === '/api/marketplace/finalize-expired' && $requestMethod === 'POST':
        $marketplaceController->finalizeExpiredListings();
        break;

    // ---------------- Bid Routes --------------------

    // Get bids for a specific listing
    case $requestUri === '/api/bids/listing' && $requestMethod === 'GET':
        $bidController->getBidsForListing();
        break;

    // Get highest bid for a specific listing
    case preg_match('/\/api\/marketplace\/highestBid\/(\d+)/', $requestUri, $matches) && $requestMethod === 'GET':
        $listingId = $matches[1];
        $marketplaceController->getHighestBid($listingId);
        break;

    // Place a bid on a listing
    case $requestUri === '/api/bids' && $requestMethod === 'POST':
        $decodedUser = $authMiddleware->verifyToken();
        $bidController->placeBid($decodedUser->id);
        break;

    // Buy a Pokémon card
    case $requestUri === '/api/marketplace/buy-now' && $requestMethod === 'POST':
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

    // ---------------- Admin Routes --------------------

    // Get all users (Admin only)
    case $requestUri === '/api/users' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->getAllUsers($decodedUser);
        break;

    // Get all cards (Admin only)
    case $requestUri === '/api/admin/cards' && $requestMethod === 'GET':
        $authMiddleware->verifyToken();
        $cardController->getAllCards();
        break;

    // Get all transactions (Admin only)
    case $requestUri === '/api/admin/transactions' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $transactionController->getAllTransactions();
        break;

    // Get all bids (Admin only)
    case $requestUri === '/api/admin/bids' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $bidController->getAllBids();
        break;

    // Get all listings (Admin only)
    case $requestUri === '/api/admin/listings' && $requestMethod === 'GET':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->getAllListings($decodedUser);
        break;

    // Update a card (Admin only)
    case preg_match('/\/api\/admin\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT':
        $authMiddleware->verifyToken();
        $cardController->updateCard((int)$matches[1]);
        break;

    // Update a listing (Admin only)
    case preg_match('/\/api\/admin\/listings\/(\d+)/', $requestUri, $matches) && $requestMethod === 'PUT':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->updateListing($decodedUser, (int)$matches[1]);
        break;
        
    // Delete a listing (Admin only)
    case preg_match('/\/api\/admin\/listings\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $marketplaceController->deleteListing($decodedUser, (int)$matches[1]);
        break;

    // Delete a bid (Admin only)
    case preg_match('/\/api\/admin\/bids\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $bidController->deleteBid((int)$matches[1]);
        break;

    // Delete transaction (Admin only)
    case preg_match('/\/api\/admin\/transactions\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $transactionController->deleteTransaction($decodedUser, (int)$matches[1]);
        break;

    // Delete user (Admin only)
    case preg_match('/\/api\/users\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $decodedUser = $authMiddleware->verifyToken();
        $userController->deleteUser((int)$matches[1]);
        break;

    // Delete a card (Admin only)
    case preg_match('/\/api\/admin\/cards\/(\d+)/', $requestUri, $matches) && $requestMethod === 'DELETE':
        $authMiddleware->verifyToken();
        $cardController->deleteCard((int)$matches[1]);
        break;

    // -----------Default 404 Response------------
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found", "requested_uri" => $requestUri]);
        break;
}