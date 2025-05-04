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

    public function __construct(
        BidRepository $bidRepository,
        UserRepository $userRepository,
        MarketplaceRepository $marketplaceRepository
    ) {
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
            return ['success' => false, 'message' => 'Listing is not available.'];
        }

        if ($listing->seller_id === $bid->getBidderId()) {
            return ['success' => false, 'message' => 'You cannot bid on your own listing.'];
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

    public function getHighestBid(int $listingId): array {
        $bid = $this->bidRepository->getHighestBidForListing($listingId);
        return ['success' => true, 'data' => $bid];
    }

    public function getAllBidsForListing(int $listingId): array {
        $bids = $this->bidRepository->getAllBidsForListing($listingId);
        return ['success' => true, 'data' => $bids];
    }

    public function getBidsByUser(int $userId): array {
        $bids = $this->bidRepository->getBidsByUserId($userId);
        return ['success' => true, 'data' => $bids];
    }

    public function getAllBids($decodedUser, int $page = 1, int $limit = 10, array $filters = []): array {
        if (!isset($decodedUser->role) || $decodedUser->role !== 'admin') {
            return ['success' => false, 'message' => 'Unauthorized: Admin access required.', 'data' => null];
        }

        $offset = ($page - 1) * $limit;
        $total = $this->bidRepository->getBidsCount($filters);
        $bids = $this->bidRepository->getAllBids($limit, $offset, $filters);

        return [
            'success' => true,
            'message' => 'Bids retrieved successfully.',
            'data' => $bids,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];
    }

    public function deleteBid(int $id): array {
        $deleted = $this->bidRepository->deleteBid($id);
        return $deleted
            ? ['success' => true, 'message' => 'Bid deleted successfully.']
            : ['success' => false, 'message' => 'Failed to delete bid.'];
    }
}