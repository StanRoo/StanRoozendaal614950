<?php

namespace App\Services;

use App\Repositories\MarketplaceRepository;
use App\Repositories\CardRepository;
use App\Repositories\UserRepository;
use App\Models\MarketplaceListingModel;
use App\Utils\ErrorHandler;

class MarketplaceService
{
    private MarketplaceRepository $marketplaceRepository;
    private CardRepository $cardRepository;
    private UserRepository $userRepository;

    public function __construct(MarketplaceRepository $marketplaceRepository, CardRepository $cardRepository, UserRepository $userRepository)
    {
        $this->marketplaceRepository = $marketplaceRepository;
        $this->cardRepository = $cardRepository;
        $this->userRepository = $userRepository;
    }

    public function listCard(int $userId, int $cardId, float $price): array
    {
        $card = $this->cardRepository->getCardById($cardId);

        if (!$card) {
            return ErrorHandler::respondWithError(404, "Card not found.");
        }

        if ($card->user_id !== $userId) {
            return ErrorHandler::respondWithError(403, "Unauthorized: You do not own this card.");
        }

        $existingListing = $this->marketplaceRepository->getListingByCardId($cardId);
        if ($existingListing) {
            return ErrorHandler::respondWithError(400, "This card is already listed on the marketplace.");
        }

        $listingData = [
            'card_id' => $cardId,
            'seller_id' => $userId,
            'price' => $price,
            'status' => 'active',
            'listed_at' => date("Y-m-d H:i:s")
        ];

        $listing = new MarketplaceListingModel($listingData);
        $created = $this->marketplaceRepository->createListing($listing);

        if ($created) {
            return [
                'success' => true,
                'message' => 'Card listed successfully.',
                'data' => $listing->toArray()
            ];
        }
        return ErrorHandler::respondWithError(500, "Failed to list card.");
    }

    public function getActiveListings(): array
    {
        return $this->marketplaceRepository->getActiveListings();
    }

    public function getCardWithListing($cardId) {       
        $listing = $this->marketplaceRepository->getActiveListingByCardId($cardId);
        if (!$listing) {
            return null;
        }
    
        $card = $this->cardRepository->getCardById($cardId);
        if (!$card) {
            return null;
        }
    
        $seller = $this->userRepository->getUserById($listing->getSellerId());
        $cardArray = $card->toArray();
        $cardData = [
            'card' => $cardArray,
            'price' => $listing->getPrice(),
            'listing_id' => $listing->getId(),
            'listed_at' => $listing->getListedAt(),
            'seller_id' => $listing->getSellerId(),
            'seller_username' => $seller ? $seller->getUsername() : 'Unknown',
        ];
        return $cardData;
    }

    public function getListingById($listingId) {
        return $this->marketplaceRepository->getListingById($listingId);
    }

    public function markListingAsSold($listingId): bool {
        return $this->marketplaceRepository->markListingAsSold($listingId);
    }
}