<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\RiderVerificationStep1Request;
use App\Http\Requests\RiderVerificationStep2Request;
use App\Http\Requests\RiderVerificationStep3Request;
use App\Services\RiderVerificationService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RiderVerificationController extends Controller
{
    protected $RiderVerificationService;

    public function __construct(RiderVerificationService $RiderVerificationService)
    {
        $this->RiderVerificationService = $RiderVerificationService;
    }
    public function step1(RiderVerificationStep1Request $request)
    {
        Log::info("rirder verification step 1" ,[$request->all()]);
        $data = $request->validated();
        $verification = $this->RiderVerificationService->storeStep(1, $data);
        return ResponseHelper::success($verification, "Step 1 saved.");
    }

    public function step2(RiderVerificationStep2Request $request)
    {
        $data = $request->validated();
        $verification = $this->RiderVerificationService->storeStep(2, $data);
        return ResponseHelper::success($verification, "Step 2 saved.");
    }

    public function step3(RiderVerificationStep3Request $request)
    {
        $data = [];

        foreach (['passport_photo', 'rider_permit_upload', 'vehicle_video'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store("rider_verifications/$field", 'public');
            }
        }

        $verification = $this->RiderVerificationService->storeStep(3, $data);
        return ResponseHelper::success($verification, "Step 3 files uploaded.");
    }
}
