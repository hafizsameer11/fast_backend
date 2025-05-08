<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ParcelBidService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\ParcelBidRequest;

class ParcelBidController extends Controller
{
    protected $service;

    public function __construct(ParcelBidService $service)
    {
        $this->service = $service;
    }

    public function store(ParcelBidRequest $request)
    {
        $data = $request->validated();
        $data['rider_id'] = auth()->id(); // ✅ this must be set if rider is bidding
        $data['created_by'] = 'rider'; // ensure this is set

        $bid = $this->service->createBid($data);
        return ResponseHelper::success($bid, "Bid sent successfully");
    }
    public function list($parcelId)
    {
        $bids = $this->service->getParcelBids($parcelId);

        $transformed = $bids->map(function ($bid) {
            $bidder = $bid->created_by === 'rider' ? $bid->rider : $bid->user;

            return [
                'id' => $bid->id,
                'send_parcel_id' => $bid->send_parcel_id,
                'bid_amount' => $bid->bid_amount,
                'message' => $bid->message,
                'status' => $bid->status,
                'created_by' => $bid->created_by,
                'created_at' => $bid->created_at,
                'updated_at' => $bid->updated_at,
                'bidder' => $bidder ? [
                    'id' => $bidder->id,
                    'name' => $bidder->name,
                    'email' => $bidder->email,
                    'phone' => $bidder->phone ?? null,
                    'profile_picture' => $bidder->profile_picture ?? null,
                    'role' => $bidder->role,
                ] : null
            ];
        });

        return ResponseHelper::success($transformed, "Bids retrieved");
    }


    public function accept($bidId)
    {
        $bid = $this->service->getBid($bidId);

        if ($bid->created_by !== 'rider') {
            return ResponseHelper::error("This bid was not created by a rider.", 400);
        }

        if ($bid->parcel->is_assigned) {
            return ResponseHelper::error("Parcel already assigned to a rider.", 400);
        }

        $updated = $this->service->acceptBid($bidId);
        return ResponseHelper::success($updated, "Bid accepted & rider assigned");
    }

    // User-side
    public function storeByUser(ParcelBidRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id(); // ✅ user ID must be set
        $data['created_by'] = 'user';

        $bid = $this->service->createUserBid($data);
        return ResponseHelper::success($bid, "User bid sent successfully");
    }


    public function riderAccept($bidId)
    {
        $bid = $this->service->getBid($bidId);

        if ($bid->created_by !== 'user') {
            return ResponseHelper::error("This bid was not created by a user.", 400);
        }

        if ($bid->parcel->is_assigned) {
            return ResponseHelper::error("Parcel already assigned to another rider.", 400);
        }

        $updated = $this->service->acceptUserBid($bidId);
        return ResponseHelper::success($updated, "User bid accepted & parcel assigned to rider");
    }

}
