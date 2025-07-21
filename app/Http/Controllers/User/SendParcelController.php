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
use App\Models\SendParcel;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $parcel['delivery_fee_n'] = rand(1500, 5000);

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

        $parcel = $this->sendParcelService->update($id, $data, $step = 4);
        $parcel['delivery_fee_n']='2500';
        return ResponseHelper::success($parcel, 'Parcel finalized and sent');
    }


    public function index(Request $request)
    {
        try {
            $rider_lat = $request->input('latitude');
            $rider_lng = $request->input('longitude');
            Log::info("Rider latitude: $rider_lat, Rider longitude: $rider_lng");
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

        // Verify the pickup code via the service
        $result = $this->sendParcelService->verifyPickupCode($id, $request->pickup_code);

        // Debugging: Output the verification result

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
    public function podReceiver(Request $request, $id)
    {
        try {
            $bankName = $request->input('bank_name');
            $accountNumber = $request->input('account_number');
            $accountName = $request->input('account_name');

            $parcel = $this->sendParcelService->find($id);
            if (!$parcel) {
                return ResponseHelper::error("Parcel not found");
            }

            $parcelPayment = ParcelPayment::where('parcel_id', $id)->first();
            if (!$parcelPayment) {
                return ResponseHelper::error("Parcel payment record not found");
            }

            // 1. Update pod_status
            $parcelPayment->update([
                'pod_status' => 'paid'
            ]);

            // 2. Credit the user (receiver/sender depending on your logic)
            $receiverUser = User::where('id', $parcel->user_id)->first();
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $receiverUser->id],
                ['balance' => 0]
            );

            $wallet->balance += $parcel->amount;
            $wallet->save();

            // 3. Create transaction for parcel amount only
            Transaction::create([
                'user_id' => $receiverUser->id,
                'transaction_type' => 'pod_payment',
                'amount' => $parcel->amount,
                'status' => 'completed',
                'reference' => 'POD-PAYMENT-' . strtoupper(uniqid()),
            ]);

            return ResponseHelper::success($parcelPayment, "PoD payment marked and user credited");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
    public function details($id)
    {
        try {
            $parcel = $this->sendParcelService->details($id);
            return ResponseHelper::success($parcel, "Parcel details retrieved successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }
    public function checkBidAccepted($parcelId)
    {
        $user = Auth::user();
        $parcel = SendParcel::where('id', $parcelId)->first();
        //check current rider bid is assiged or not or does rider id is not null
        $currentRider = false;
        if ($parcel->is_assigned && $parcel->rider_id != null) {
            //now check weather current rider is being asigned or not
            if ($parcel->rider_id == $user->id) {
                $currentRider = true;
            }
        }
        $parcel = $currentRider ? $parcel : null;
        return ResponseHelper::success($parcel, "Parcel details retrieved successfully");
    }
    public function getActiveParcelRider()
    {
        $user = Auth::user();
        $parcel = SendParcel::where('rider_id', $user->id)->with('rider', 'acceptedBid','user')
            ->whereIn('status', ['in_transit', 'ordered'])
            ->orderBy('created_at','desc')
            ->first();

        return ResponseHelper::success($parcel, "Parcel details retrieved successfully");
    }
    public function getActiveParcelUser()
    {
        $user = Auth::user();
        $parcel = SendParcel::where('user_id', $user->id)->whereNotNull('rider_id')->with('rider', 'acceptedBid')
            ->whereIn('status', ['in_transit', 'ordered'])
            ->latest()
            ->first();

        return ResponseHelper::success($parcel, "Parcel details retrieved successfully");
    }
}
