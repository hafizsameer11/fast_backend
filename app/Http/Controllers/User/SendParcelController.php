<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateParcelStatusRequest;
use App\Http\Requests\User\StepFourRequest;
use App\Http\Requests\User\StepOneRequest;
use App\Http\Requests\User\StepThreeRequest;
use App\Http\Requests\User\StepTwoRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\SendParcelService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SendParcelRequest;
use App\Models\ParcelPayment;

class SendParcelController extends Controller
{
    protected $sendParcelService;


    public function __construct(SendParcelService $sendParcelService)
    {
        $this->sendParcelService = $sendParcelService;
    }
    public function cancelParcel($id, Request $request)
    {
        try {
            $reason = $request->input('reason');
            $parcel = $this->sendParcelService->cancelParcel($id, $reason);
            return ResponseHelper::success($parcel, "Parcel canceled successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
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
    public function createStepOne(StepOneRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['status'] = 'draft';

        $parcel = $this->sendParcelService->create($data);
        return ResponseHelper::success($parcel, 'Step 1 completed');
    }

    public function stepTwo(StepTwoRequest $request, $id)
    {
        $parcel = $this->sendParcelService->update($id, $request->validated());
        return ResponseHelper::success($parcel, 'Step 2 completed');
    }

    public function stepThree(StepThreeRequest $request, $id)
    {
        $parcel = $this->sendParcelService->update($id, $request->validated());
        return ResponseHelper::success($parcel, 'Step 3 completed');
    }

    public function stepFour(StepFourRequest $request, $id)
    {
        $data = $request->validated();

        // 🔁 Map boolean to 'yes' or 'no'
        $data['pay_on_delivery'] = $data['pay_on_delivery'] ? 'yes' : 'no';

        $data['status'] = 'ordered';
        $data['ordered_at'] = now();
        $data['pickup_code'] = rand(1000, 9999);
        $data['delivery_code'] = rand(1000, 9999);

        $parcel = $this->sendParcelService->update($id, $data,$step=4);
        return ResponseHelper::success($parcel, 'Parcel finalized and sent');
    }


    public function index(Request $request)
    {
        try {
            $rider_lat = $request->input('latitude');
            $rider_lng = $request->input('longitude');
            $parcels = $this->sendParcelService->all($rider_lat, $rider_lng);
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

    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $parcel = $this->sendParcelService->find($id);

        if (!$parcel || auth()->id() != $parcel->rider_id) {
            return ResponseHelper::error("Unauthorized or parcel not found.");
        }

        $parcel->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);

        return ResponseHelper::success($parcel, "Location updated.");
    }
    public function payByBankSender(Request $request, $id)
    {
        try {
            //get parcel
            $parcel = $this->sendParcelService->find($id);
            if (!$parcel) {
                return ResponseHelper::error("Parcel not found");
            }
            //create transaction for delivery fee
            $deliveryFeePayment = new Transaction();
            $deliveryFeePayment->user_id = auth()->id();
            $deliveryFeePayment->transaction_type = 'delivery_fee';
            $deliveryFeePayment->amount = $parcel->delivery_fee;
            $deliveryFeePayment->status = 'completed';
            $deliveryFeePayment->reference = 'DELIVERY-FEE-' . strtoupper(uniqid());
            $deliveryFeePayment->save();
            $parcelPayment = ParcelPayment::where('parcel_id', $id)->first();
            $parcelPayment->update([
                'delivery_fee_status' => 'paid',
            ]);
            return ResponseHelper::success($deliveryFeePayment, "Parcel sent successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
