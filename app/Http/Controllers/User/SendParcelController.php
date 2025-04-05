<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateParcelStatusRequest;
use Illuminate\Http\Request;
use App\Services\SendParcelService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SendParcelRequest;


class SendParcelController extends Controller
{
    protected $sendParcelService;


    public function __construct(SendParcelService $sendParcelService)
    {
        $this->sendParcelService = $sendParcelService;
    }

    public function create(SendParcelRequest $request)
    {
        try {
            $parcel = $this->sendParcelService->create($request->validated());
            return ResponseHelper::success($parcel, "Parcel sent successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function index()
    {
        try {
            $parcels = $this->sendParcelService->all()->map(fn($p) => $p->fresh());
            return ResponseHelper::success($parcels, "Parcel list retrieved successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }

    public function updateStatus(UpdateParcelStatusRequest $request, $id)
    {
        try {
            $updated = $this->sendParcelService->updateStatus($id, $request->status);
            return ResponseHelper::success($updated, "Parcel status updated successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }
    public function confirmPickup(Request $request, $id)
    {
        // Validate pickup_code
        $request->validate(['pickup_code' => 'required|digits:4']);
    
        // Debugging: Output the pickup_code received from the request
        echo "Received Pickup Code: " . $request->pickup_code . "\n";
    
        // Verify the pickup code via the service
        $result = $this->sendParcelService->verifyPickupCode($id, $request->pickup_code);
    
        // Debugging: Output the verification result
        echo "Verification Result: " . ($result ? 'Success' : 'Failed') . "\n";
    
        // Check the result of the verification
        if (!$result) {
            return ResponseHelper::error("Invalid pickup code.");
        }
    
        return ResponseHelper::success(null, "Pickup confirmed.");
    }
    

    public function confirmDelivery(Request $request, $id)
    {
        $request->validate(['delivery_code' => 'required|digits:4']);

        $result = $this->sendParcelService->verifyDeliveryCode($id, $request->delivery_code);

        if (!$result) {
            return ResponseHelper::error("Invalid delivery code.");
        }

        return ResponseHelper::success(null, "Delivery confirmed.");
    }
}
