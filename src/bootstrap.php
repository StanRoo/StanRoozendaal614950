<?php

// Autoload all the necessary files via Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config;

// Repositories
use App\Repositories\CardRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\MarketplaceRepository;
use App\Repositories\BidRepository;

// Services
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\CardService;
use App\Services\TransactionService;
use App\Services\MarketplaceService;
use App\Services\BidService;

// Middleware
use App\Middleware\AuthMiddleware;

// Controllers
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\CardController;
use App\Controllers\TransactionController;
use App\Controllers\MarketplaceController;
use App\Controllers\BidController;

// ErrorHandler
use App\Utils\ErrorHandler;

// Get the PDO instance from Config
$pdo = \App\Config::getPDO();

// Initialize repositories
$cardRepository = new CardRepository($pdo);
$transactionRepository = new TransactionRepository($pdo);
$userRepository = new UserRepository($pdo);
$marketplaceRepository = new MarketplaceRepository($pdo);
$bidRepository = new BidRepository($pdo);

// Initialize services with dependency injection
$authService = new AuthService($userRepository);
$userService = new UserService($userRepository);
$cardService = new CardService($cardRepository);
$transactionService = new TransactionService($transactionRepository, $cardRepository);
$marketplaceService = new MarketplaceService($marketplaceRepository, $cardRepository, $userRepository, $bidRepository, $userService, $cardService, $transactionService);
$bidService = new BidService($bidRepository, $userRepository, $marketplaceRepository);

// Initialize middleware or error handling
$errorHandler = new ErrorHandler();
$authMiddleware = new AuthMiddleware($userRepository);

// Initialize controllers with the corresponding services
$authController = new AuthController($authService, $errorHandler);
$userController = new UserController($userService, $authMiddleware, $errorHandler);
$cardController = new CardController($cardService, $userService, $authMiddleware, $errorHandler);
$transactionController = new TransactionController($transactionService, $authMiddleware, $errorHandler);
$marketplaceController = new MarketplaceController($marketplaceService, $authMiddleware, $errorHandler);
$bidController = new BidController($bidService, $marketplaceService, $authMiddleware, $errorHandler);