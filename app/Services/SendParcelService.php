<?php

namespace App\Services;

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

    public function all($latitude, $longitude)
    {
        return $this->sendParcelRepository->all($latitude, $longitude);
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

    public function update($id, array $data)
    {
        return $this->sendParcelRepository->update($id, $data);
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

        echo "Verifying Delivery Code for Parcel ID: " . $id . "\n";

        if (!$parcel) {
            echo "Parcel not found with ID: " . $id . "\n";
            return false;
        }

        echo "Expected Delivery Code: " . (string) $parcel->delivery_code . "\n";
        echo "Received Delivery Code: " . (string) $code . "\n";

        if ((string) $parcel->delivery_code !== (string) $code) {
            echo "Delivery Code Verification Failed for Parcel ID: " . $id . "\n";
            return false;
        }

        $parcel->update([
            'is_delivery_confirmed' => 'yes',
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $parcel->refresh(); // ✅ re-fetch to verify

        echo "After Update — Status: " . $parcel->status . "\n";
        echo "After Update — is_delivery_confirmed: " . ($parcel->is_delivery_confirmed ? 'Yes' : 'No') . "\n";
        echo "Delivery confirmed for Parcel ID: " . $id . "\n";
        echo "Status updated to 'delivered'.\n";

        return true;
    }
    public function getParcelForUser($userId)
    {
        return $this->sendParcelRepository->getParcelForUser($userId);
    }

}
