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
        $data = $request->validated(); // ✅ use validated directly
        $data['rider_id'] = auth()->id();

        $bid = $this->service->createBid($data);
        return ResponseHelper::success($bid, "Bid sent successfully");
    }

    public function list($parcelId)
    {
        $bids = $this->service->getParcelBids($parcelId);
        return ResponseHelper::success($bids, "Bids retrieved");
    }

    public function accept($bidId)
    {
        $updated = $this->service->acceptBid($bidId);
        return ResponseHelper::success($updated, "Bid accepted & rider assigned");
    }

    public function storeByUser(ParcelBidRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['created_by'] = 'user';

        $bid = $this->service->createUserBid($data);
        return ResponseHelper::success($bid, "User bid sent successfully");
    }
    
    public function riderAccept($bidId)
    {
        $updated = $this->service->acceptUserBid($bidId);
        return ResponseHelper::success($updated, "User bid accepted & parcel assigned to rider");
    }
}
