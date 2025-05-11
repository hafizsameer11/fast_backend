<?php

namespace App\Services;

use App\Models\ParcelPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\SendParcelRepository;
use \Exception;
use Illuminate\Support\Facades\Log;

class SendParcelService
{
    protected $sendParcelRepository;

    public function __construct(SendParcelRepository $sendParcelRepository)
    {
        $this->sendParcelRepository = $sendParcelRepository;
    }
    public function details($id)
    {
        return $this->sendParcelRepository->details($id);
    }

    public function all($latitude, $longitude)
    {
        return $this->sendParcelRepository->all($latitude, $longitude);
    }
    public function cancelParcel($id, $reason)
    {
        return $this->sendParcelRepository->cancelParcel($id, $reason);
    }

    public function find($id)
    {
        return $this->sendParcelRepository->find($id);
    }

    public function create(array $data)
    {
        try {
            // Generate 4-digit codes
            $data['pickup_code'] = rand(1000, 9999);
            $data['delivery_code'] = rand(1000, 9999);

            // Output the codes for debugging (console output)
            echo "Pickup Code: " . $data['pickup_code'] . "\n";
            echo "Delivery Code: " . $data['delivery_code'] . "\n";

            // Log the codes as well for persistence
            Log::info("Generated Pickup Code: " . $data['pickup_code']);
            Log::info("Generated Delivery Code: " . $data['delivery_code']);

            return $this->sendParcelRepository->create($data);
        } catch (\Throwable $th) {
            throw new Exception("Error creating SendParcel: " . $th->getMessage());
        }
    }

    public function update($id, array $data, $step = null)
    {
        return $this->sendParcelRepository->update($id, $data, $step);
    }

    public function delete($id)
    {
        return $this->sendParcelRepository->delete($id);
    }

    public function updateStatus($id, $status)
    {
        return $this->sendParcelRepository->updateStatus($id, $status);
    }

    public function verifyPickupCode($id, $code)
    {
        $parcel = $this->sendParcelRepository->find($id);

        echo "Verifying Pickup Code for Parcel ID: " . $id . "\n";

        if (!$parcel) {
            echo "Parcel not found with ID: " . $id . "\n";
            return false;
        }

        echo "Expected Pickup Code: " . (string) $parcel->pickup_code . "\n";
        echo "Received Pickup Code: " . (string) $code . "\n";

        if ((string) $parcel->pickup_code !== (string) $code) {
            echo "Pickup Code Verification Failed for Parcel ID: " . $id . "\n";
            return false;
        }

        $parcel->update([
            'is_pickup_confirmed' => 'yes',
            'status' => 'in_transit',
            'in_transit_at' => now(),
        ]);

        $parcel->refresh(); // ✅ force re-fetch the updated fields

        echo "After Update — Status: " . $parcel->status . "\n";
        echo "After Update — is_pickup_confirmed: " . $parcel->is_pickup_confirmed . "\n";
        echo "Pickup confirmed for Parcel ID: " . $id . "\n";
        echo "Status updated to 'in_transit'.\n";

        return true;
    }

    public function verifyDeliveryCode($id, $code)
    {
        $parcel = $this->sendParcelRepository->find($id);
        if (!$parcel) {
            return false;
        }
        if ((string) $parcel->delivery_code !== (string) $code) {
            return false;
        }
        $parcel->update([
            'is_delivery_confirmed' => 'yes',
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $totalAmount = $parcel->amount + $parcel->delivery_fee;
        $parcelPayment = ParcelPayment::where('parcel_id', $id)->first();
        if ($parcelPayment) {
            $parcelPayment->update([
                'payment_status' => 'completed',
                'delivery_fee_status' => 'paid',
            ]);
            $totalAmount = $parcelPayment->total_amount;
        }
        $parcel->refresh();
        $admin = User::where('role', 'admin')->first();
        $adminWallet = Wallet::where('user_id', $admin->id)->first();
        if ($parcel->payer == 'sender') {
            $deliveryFee = $parcel->delivery_fee;
            $riderShare = round($deliveryFee * 0.8, 2);
            $adminShare = $deliveryFee - $riderShare;

            // Admin wallet must exist
            if (!$adminWallet) {
                $adminWallet = Wallet::create([
                    'user_id' => $admin->id,
                    'balance' => 0,
                ]);
            }
            $adminWallet->balance += $adminShare;
            $adminWallet->save();

            // Rider wallet
            $riderId = $parcel->acceptedBid->rider->id;
            $riderWallet = Wallet::firstOrCreate(
                ['user_id' => $riderId],
                ['balance' => 0]
            );
            $riderWallet->balance += $riderShare;
            $riderWallet->save();

            // Admin transaction
            Transaction::create([
                'user_id' => $admin->id,
                'transaction_type' => 'delivery_fee',
                'amount' => $adminShare,
                'status' => 'completed',
                'reference' => 'ADMIN-FEE-' . strtoupper(uniqid()),
            ]);

            // Rider transaction
            Transaction::create([
                'user_id' => $riderId,
                'transaction_type' => 'delivery_fee',
                'amount' => $riderShare,
                'status' => 'completed',
                'reference' => 'RIDER-FEE-' . strtoupper(uniqid()),
            ]);

            // Deduct full fee from user if using wallet
            if ($parcel->payment_method === 'wallet') {
                $userWallet = Wallet::firstOrCreate(
                    ['user_id' => $parcel->user_id],
                    ['balance' => 0]
                );
                $userWallet->balance -= $deliveryFee;
                $userWallet->save();

                // User transaction
                Transaction::create([
                    'user_id' => $parcel->user_id,
                    'transaction_type' => 'delivery_fee',
                    'amount' => $deliveryFee,
                    'status' => 'completed',
                    'reference' => 'USER-FEE-' . strtoupper(uniqid()),
                ]);
            }
        }

        return true;
    }
    public function getParcelForUser($userId)
    {
        return $this->sendParcelRepository->getParcelForUser($userId);
    }
    public function getParcelForRider($userId)
    {
        return $this->sendParcelRepository->getParcelForRider($userId);
    }
}
