<?php

namespace App\Services;

use App\Models\BidModel;
use App\Repositories\BidRepository;
use App\Repositories\UserRepository;
use App\Repositories\MarketplaceRepository;

class BidService {
    private BidRepository $bidRepository;
    private UserRepository $userRepository;
    private MarketplaceRepository $marketplaceRepository;

    public function __construct(BidRepository $bidRepository, UserRepository $userRepository, MarketplaceRepository $marketplaceRepository) {
        $this->bidRepository = $bidRepository;
        $this->userRepository = $userRepository;
        $this->marketplaceRepository = $marketplaceRepository;
    }

    public function placeBid(BidModel $bid, float $minimumBid): array {
        $highestBid = $this->bidRepository->getHighestBidByListingId($bid->getListingId());
        $user = $this->userRepository->getUserById($bid->getBidderId());
        $listing = $this->marketplaceRepository->getListingById($bid->getListingId());

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if (!$listing || $listing->status !== 'active') {
            return ['success' => false, 'message' => 'Listing not available.'];
        }

        if ($bid->getBidAmount() < $minimumBid) {
            return ['success' => false, 'message' => "Bid must be at least {$minimumBid} CuboCoins."];
        }

        if ($highestBid && $bid->getBidAmount() <= $highestBid->getBidAmount()) {
            return ['success' => false, 'message' => 'Your bid must be higher than the current highest bid.'];
        }

        if ($user->getBalance() < $bid->getBidAmount()) {
            return ['success' => false, 'message' => 'Insufficient balance.'];
        }

        $user->balance -= $bid->getBidAmount();
        $this->userRepository->updateBalance($user->getId(), $user->getBalance());
        $this->bidRepository->createBid($bid);

        return ['success' => true, 'message' => 'Bid placed successfully.'];
    }

    public function getHighestBid(int $listingId): ?BidModel {
        return $this->bidRepository->getHighestBidForListing($listingId);
    }

    public function getAllBidsForListing(int $listingId): array {
        return $this->bidRepository->getAllBidsForListing($listingId);
    }

    public function getBidsByUser(int $userId): array {
        return $this->bidRepository->getBidsByUserId($userId);
    }
}