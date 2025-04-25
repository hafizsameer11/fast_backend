<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function all() {}

    public function find($id)
    {
        // Add logic to find data by ID
    }
    public function createUserWallet($userId)
    {
        $wallet = Wallet::create([
            'user_id' => $userId,
        ]);
        return $wallet;
        // Add logic to create a wallet for the user
    }
    public function create(array $data)
    {
        if (isset($data["password"]) && $data["password"] != "") {
            $data["password"] = bcrypt($data["password"]);
        }
        if (isset($data['profile_picture']) && $data['profile_picture']) {
            $path = $data['profile_picture']->store('profile_picture', 'public');
            $data['profile_picture'] = $path;
        }
        $user = User::create($data);
        $this->createUserWallet($user->id);
        return $user;
    }

    public function update($id, array $data)
    {
        $user = User::find($id);
        if (!$user) {
            throw new \Exception("User not found.");
        }
        $user->update($data);
        return $user;
    }
    public function delete($id) {}
    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }
    public function getUserManagement()
    {
        $totalUsers = User::count();
        $users = User::where('role', '!=', 'admin')->get();
        $activeUsers = User::where('is_active', 1)->count();
        $inactiveUsers = User::where('is_active', 0)->count();
        $data = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'users' => $users,
        ];
        return $data;
    }
    public function getUserDetails($userId)
    {
        $user = User::where('id', $userId)->where('role', '!=', value: 'admin')->with('wallet', 'sendParcel.bids', 'addresses')->first();
        if (!$user) {
            Log::info("User not found for ID: $userId", [
                'user_id' => $userId,
                'timestamp' => now(),

            ]);
            throw new \Exception("User not found. for id $userId");
        }
        return $user;
    }
}
