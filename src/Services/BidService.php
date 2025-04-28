<?php

namespace App\Services;

use App\Models\BidModel;
use App\Repositories\BidRepository;
use App\Repositories\UserRepository;
use App\Repositories\MarketplaceRepository;
use App\Utils\ResponseHelper;

class BidService {
    private BidRepository $bidRepository;
    private UserRepository $userRepository;
    private MarketplaceRepository $marketplaceRepository;

    public function __construct(
        BidRepository $bidRepository,
        UserRepository $userRepository,
        MarketplaceRepository $marketplaceRepository
    ) {
        $this->bidRepository = $bidRepository;
        $this->userRepository = $userRepository;
        $this->marketplaceRepository = $marketplaceRepository;
    }

    public function placeBid(BidModel $bid, float $minimumBid): void {
        $highestBid = $this->bidRepository->getHighestBidByListingId($bid->getListingId());
        $user = $this->userRepository->getUserById($bid->getBidderId());
        $listing = $this->marketplaceRepository->getListingById($bid->getListingId());

        if (!$user) {
            ResponseHelper::error('User not found.', 404);
        }

        if (!$listing || $listing->status !== 'active') {
            ResponseHelper::error('Listing is not available.', 400);
        }

        if ($listing->user_id === $bid->getBidderId()) {
            ResponseHelper::error('You cannot bid on your own listing.', 400);
        }

        if ($bid->getBidAmount() < $minimumBid) {
            ResponseHelper::error("Bid must be at least {$minimumBid} CuboCoins.", 400);
        }

        if ($highestBid && $bid->getBidAmount() <= $highestBid->getBidAmount()) {
            ResponseHelper::error('Your bid must be higher than the current highest bid.', 400);
        }

        if ($user->getBalance() < $bid->getBidAmount()) {
            ResponseHelper::error('Insufficient balance.', 400);
        }

        $user->balance -= $bid->getBidAmount();
        $this->userRepository->updateBalance($user->getId(), $user->getBalance());
        $this->bidRepository->createBid($bid);

        ResponseHelper::success(null, 'Bid placed successfully.');
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

    public function getAllBids(): array {
        return $this->bidRepository->getAllBids();
    }

    public function deleteBid(int $id): bool {
        return $this->bidRepository->deleteBid($id);
    }
}