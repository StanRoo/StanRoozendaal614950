<?php

namespace App\Services;

use App\Repositories\MarketplaceRepository;
use App\Repositories\CardRepository;
use App\Repositories\UserRepository;
use App\Repositories\BidRepository;
use App\Models\MarketplaceListingModel;
use App\Models\BidModel;
use App\Models\UserModel;

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

    public function getAllListings($decodedUser, int $page = 1, int $limit = 10, array $filters = []): array
    {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return ['success' => false, 'message' => 'Unauthorized: Admin access required.', 'data' => null];
        }
        $offset = ($page - 1) * $limit;
        $total = $this->marketplaceRepository->getListingsCount($filters);
        $listings = $this->marketplaceRepository->getAllListings($limit, $offset, $filters);
        return [
            'success' => true,
            'message' => 'Listings retrieved successfully.',
            'data' => $listings,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];
    }

    public function getFilteredListings(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        try {
            $total = $this->marketplaceRepository->countFilteredListings($userId, $filters);
            $listings = $this->marketplaceRepository->getFilteredListings($userId, $offset, $limit, $filters);

            return [
                'success' => true,
                'message' => 'Marketplace listings retrieved successfully.',
                'data' => $listings,
                'pagination' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'total' => $total,
                    'hasMore' => ($offset + $limit) < $total
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching listings: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getUserFilteredListings(int $userId, int $offset = 0, int $limit = 20, array $filters = []): array
    {
        try {
            $total = $this->marketplaceRepository->countFilteredUserListings($userId, $filters);
            $listings = $this->marketplaceRepository->getFilteredUserListings($userId, $offset, $limit, $filters);

            return [
                'success' => true,
                'message' => 'User listings retrieved successfully.',
                'data' => $listings,
                'pagination' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'total' => $total,
                    'hasMore' => ($offset + $limit) < $total,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'An error occurred while fetching user listings: ' . $e->getMessage(),
                'data' => null
            ];
        }
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
            'success' => true,
            'message' => 'Card details retrieved successfully.',
            'data' => [
                'card' => $card->toArray(),
                'price' => $listing->getPrice(),
                'listing_id' => $listing->getId(),
                'listed_at' => $listing->getListedAt(),
                'expires_at' => $listing->getExpiresAt(),
                'seller_id' => $listing->getSellerId(),
                'seller_username' => $seller ? $seller->getUsername() : 'Unknown',
                'min_bid_price' => $listing->getMinBidPrice()
            ]
        ];
    }

    public function getListingById($listingId) {
        return $this->marketplaceRepository->getListingById($listingId);
    }

    public function getHighestBidForListing(int $listingId): ?array {
        $bid = $this->bidRepository->getHighestBidByListingId($listingId);

        if (!$bid) {
            return [
                'success' => true,
                'message' => 'No bids found.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Highest bid retrieved successfully.',
            'data' => [
                'listing_id' => $bid->getListingId(),
                'bidder_id' => $bid->getBidderId(),
                'bid_amount' => $bid->getBidAmount(),
                'created_at' => $bid->getCreatedAt(),
            ]
        ];
    }

    public function getHighestBidder(int $userId): ?UserModel {
        return $this->userRepository->getUserById($userId);
    }

    public function updateListing(int $id, array $data): void {
        $this->marketplaceRepository->updateListing($id, $data);
    }

    public function markListingAsSold($listingId): bool {
        return $this->marketplaceRepository->markListingAsSold($listingId);
    }

    public function listCard(int $userId, int $cardId, float $price, float $minBid, ?string $expiresAt): array {
        $card = $this->cardRepository->getCardById($cardId);

        if (!$card) {
            return ['success' => false, 'message' => 'Card not found.', 'data' => null];
        }

        if ($card->user_id !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized: You do not own this card.', 'data' => null];
        }

        if ($card->is_listed) {
            return ['success' => false, 'message' => 'This card is already listed.', 'data' => null];
        }

        $existingListing = $this->marketplaceRepository->getListingByCardId($cardId);
        if ($existingListing) {
            return ['success' => false, 'message' => 'This card already has a listing.', 'data' => null];
        }

        $listing = new MarketplaceListingModel([
            'card_id' => $cardId,
            'seller_id' => $userId,
            'price' => $price,
            'status' => 'active',
            'listed_at' => date("Y-m-d H:i:s"),
            'min_bid_price' => $minBid,
            'expires_at' => $expiresAt ?? null,
        ]);

        $created = $this->marketplaceRepository->createListing($listing);

        if ($created) {
            $this->cardRepository->setCardListedStatus($cardId, 1);
            return ['success' => true, 'message' => 'Card listed successfully.', 'data' => $created->toArray()];
        }

        return ['success' => false, 'message' => 'Failed to list card.', 'data' => null];
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

    public function buyCard($listingId, $buyerId): array {
        $listing = $this->getListingById($listingId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found.', 'data' => null];
        }

        if ($buyerId === $listing->getSellerId()) {
            return ['success' => false, 'message' => 'You cannot buy your own card.', 'data' => null];
        }

        $cardId = $listing->getCardId();
        $sellerId = $listing->getSellerId();
        $price = $listing->getPrice();

        $buyer = $this->userService->getUserById($buyerId);

        if ($buyer->balance < $price) {
            return ['success' => false, 'message' => 'Insufficient balance.', 'data' => null];
        }

        $this->userService->updateUserBalance($buyer->id, $price);
        $this->userService->addOwnerBalance($sellerId, $price);
        $this->marketplaceRepository->markListingAsSold($listingId);
        $this->cardService->updateCardOwner($cardId, $buyerId);

        $transactionCreated = $this->transactionService->logTransaction($buyerId, $sellerId, $cardId, $price);

        if ($transactionCreated) {
            return ['success' => true, 'message' => 'Card purchased successfully.', 'data' => null];
        } else {
            return ['success' => false, 'message' => 'Transaction failed.', 'data' => null];
        }
    }

    public function deleteListing(int $id): void {
        $this->marketplaceRepository->deleteListing($id);
    }
}