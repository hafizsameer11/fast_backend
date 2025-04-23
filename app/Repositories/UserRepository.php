<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;

class UserRepository
{
    public function all()
    {
        // Add logic to fetch all data
    }

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
    public function delete($id)
    {
      
    }
    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }
}
