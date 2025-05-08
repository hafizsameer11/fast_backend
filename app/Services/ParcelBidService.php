<?php

namespace App\Services;

use App\Repositories\ParcelBidRepository;
use App\Models\SendParcel;

class ParcelBidService
{
    protected $repo;

    public function __construct(ParcelBidRepository $repo)
    {
        $this->repo = $repo;
    }

    public function createBid(array $data)
    {
        \Log::info('Creating Rider Bid:', $data);
        return $this->repo->create($data);
    }


    public function getParcelBids($parcelId)
    {
        return $this->repo->getBidsForParcel($parcelId);
    }

    public function acceptBid($bidId)
    {
        $bid = $this->repo->acceptBid($bidId);

        // assign to parcel
        $parcel = $bid->parcel;
        $parcel->rider_id = $bid->rider_id;
        $parcel->accepted_bid_id = $bid->id;
        $parcel->is_assigned = true;
        $parcel->status = 'picked_up';
        $parcel->picked_up_at = now();

        // 💡 generate codes if missing
        if (!$parcel->pickup_code) {
            $parcel->pickup_code = rand(1000, 9999);
        }
        if (!$parcel->delivery_code) {
            $parcel->delivery_code = rand(1000, 9999);
        }

        $parcel->save();

        $this->repo->rejectOtherBids($parcel->id, $bid->id);

        return $parcel->load('acceptedBid', 'bids');
    }
    public function createUserBid(array $data)
    {
        return $this->repo->create($data);
    }

    public function acceptUserBid($bidId)
    {
        $bid = $this->repo->acceptBid($bidId); // same accept logic

        $parcel = $bid->parcel;
        $parcel->rider_id = auth()->id(); // now assigning current rider
        $parcel->accepted_bid_id = $bid->id;
        $parcel->is_assigned = true;
        $parcel->status = 'picked_up';
        $parcel->picked_up_at = now();
        $parcel->save();

        $this->repo->rejectOtherBids($parcel->id, $bid->id);

        return $parcel->load('acceptedBid', 'bids');
    }
    public function getBid($bidId)
    {
        return $this->repo->find($bidId);
    }


}
