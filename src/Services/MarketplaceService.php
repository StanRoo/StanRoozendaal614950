<?php

namespace App\Services;

use App\Repositories\MarketplaceRepository;
use App\Repositories\CardRepository;
use App\Repositories\UserRepository;
use App\Repositories\BidRepository;
use App\Models\MarketplaceListingModel;
use App\Models\BidModel;
use App\Utils\ResponseHelper;

class MarketplaceService
{
    private MarketplaceRepository $marketplaceRepository;
    private CardRepository $cardRepository;
    private UserRepository $userRepository;
    private BidRepository $bidRepository;
    private UserService $userService;
    private CardService $cardService;
    private TransactionService $transactionService;

    public function __construct(
        MarketplaceRepository $marketplaceRepository,
        CardRepository $cardRepository,
        UserRepository $userRepository,
        BidRepository $bidRepository,
        UserService $userService,
        CardService $cardService,
        TransactionService $transactionService
    ) {
        $this->marketplaceRepository = $marketplaceRepository;
        $this->cardRepository = $cardRepository;
        $this->userRepository = $userRepository;
        $this->bidRepository = $bidRepository;
        $this->userService = $userService;
        $this->cardService = $cardService;
        $this->transactionService = $transactionService;
    }

    public function listCard(int $userId, int $cardId, float $price, float $minBid, ?string $expiresAt): void {
        $card = $this->cardRepository->getCardById($cardId);

        if (!$card) {
            ResponseHelper::error('Card not found.', 404);
        }

        if ($card->user_id !== $userId) {
            ResponseHelper::error('Unauthorized: You do not own this card.', 403);
        }

        if ($card->is_listed) {
            ResponseHelper::error('This card is already listed.', 400);
        }

        $existingListing = $this->marketplaceRepository->getListingByCardId($cardId);
        if ($existingListing) {
            ResponseHelper::error('This card already has a listing.', 400);
        }

        $listingData = [
            'card_id' => $cardId,
            'seller_id' => $userId,
            'price' => $price,
            'status' => 'active',
            'listed_at' => date("Y-m-d H:i:s"),
            'min_bid_price' => $minBid,
            'expires_at' => $expiresAt ?? null,
        ];

        $listing = new MarketplaceListingModel($listingData);
        $created = $this->marketplaceRepository->createListing($listing);

        if ($created) {
            $this->cardRepository->setCardListedStatus($cardId, 1);
            ResponseHelper::success($created->toArray(), 'Card listed successfully.');
        }

        ResponseHelper::error('Failed to list card.', 500);
    }

    public function getAllActiveListingsExceptUser($userId): array {
        return $this->marketplaceRepository->getAllActiveListingsExceptUser($userId);
    }

    public function getUserListings(int $userId): array {
        return $this->marketplaceRepository->getListingsByUserId($userId);
    }

    public function getCardWithListing($cardId): ?array {
        $listing = $this->marketplaceRepository->getActiveListingByCardId($cardId);
        if (!$listing) {
            return null;
        }

        $card = $this->cardRepository->getCardById($cardId);
        if (!$card) {
            return null;
        }

        $seller = $this->userRepository->getUserById($listing->getSellerId());

        return [
            'card' => $card->toArray(),
            'price' => $listing->getPrice(),
            'listing_id' => $listing->getId(),
            'listed_at' => $listing->getListedAt(),
            'seller_id' => $listing->getSellerId(),
            'seller_username' => $seller ? $seller->getUsername() : 'Unknown',
            'min_bid_price' => $listing->getMinBidPrice()
        ];
    }

    public function getListingById($listingId) {
        return $this->marketplaceRepository->getListingById($listingId);
    }

    public function markListingAsSold($listingId): bool {
        return $this->marketplaceRepository->markListingAsSold($listingId);
    }

    public function getHighestBidForListing(int $listingId): ?BidModel {
        return $this->bidRepository->getHighestBidByListingId($listingId);
    }

    public function finalizeExpiredListings(): array {
        $expiredListings = $this->marketplaceRepository->getExpiredActiveListings();
        $results = [];

        foreach ($expiredListings as $listing) {
            $highestBid = $this->bidRepository->getHighestBidByListingId($listing->getId());

            if ($highestBid) {
                $buyerId = $highestBid->getBidderId();
                $sellerId = $listing->getSellerId();
                $amount = $highestBid->getBidAmount();
                $cardId = $listing->getCardId();

                $this->userService->updateUserBalance($buyerId, $amount);
                $this->userService->addOwnerBalance($sellerId, $amount);
                $this->marketplaceRepository->markListingAsSold($listing->getId());
                $this->cardService->updateCardOwner($cardId, $buyerId);
                $this->cardRepository->setCardListedStatus($cardId, 0);
                $this->transactionService->logTransaction($buyerId, $sellerId, $cardId, $amount);

                $results[] = [
                    'listing_id' => $listing->getId(),
                    'status' => 'sold',
                    'new_owner' => $buyerId,
                    'amount' => $amount
                ];
            } else {
                $this->marketplaceRepository->cancelListing($listing->getId());
                $this->cardRepository->setCardListedStatus($listing->getCardId(), 0);

                $results[] = [
                    'listing_id' => $listing->getId(),
                    'status' => 'cancelled',
                    'reason' => 'No bids placed'
                ];
            }
        }

        return $results;
    }

    public function buyCard($listingId, $buyerId): void {
        $listing = $this->getListingById($listingId);
    
        if (!$listing) {
            ResponseHelper::error('Listing not found.', 404);
        }
    
        if ($buyerId === $listing->getSellerId()) {
            ResponseHelper::error('You cannot buy your own card.', 400);
        }
    
        $cardId = $listing->getCardId();
        $sellerId = $listing->getSellerId();
        $price = $listing->getPrice();
    
        $buyer = $this->userService->getUserById($buyerId);
    
        if ($buyer->balance < $price) {
            ResponseHelper::error('Insufficient balance.', 400);
        }
    
        $this->userService->updateUserBalance($buyer->id, $price);
        $this->userService->addOwnerBalance($sellerId, $price);
        $this->marketplaceRepository->markListingAsSold($listingId);
        $this->cardService->updateCardOwner($cardId, $buyerId);
    
        $transactionCreated = $this->transactionService->logTransaction($buyerId, $sellerId, $cardId, $price);
    
        if ($transactionCreated) {
            ResponseHelper::success(null, 'Card purchased successfully.');
        } else {
            ResponseHelper::error('Transaction failed.', 500);
        }
    }
    

    public function getAllListings(): array {
        return $this->marketplaceRepository->getAllListings();
    }

    public function updateListing(int $id, array $data): void {
        $this->marketplaceRepository->updateListing($id, $data);
    }

    public function deleteListing(int $id): void {
        $this->marketplaceRepository->deleteListing($id);
    }
}