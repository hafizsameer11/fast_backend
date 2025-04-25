<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserService
{
    protected $UserRepository;

    public function __construct(UserRepository $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }
    public function usermanagement()
    {
        try {
            $data = $this->UserRepository->getUserManagement();
            return $data;
        } catch (\Exception $e) {
            throw new \Exception("User not found");
        }
    }
    public function getUserDetails($userId){
        try{
            $userDetails=$this->UserRepository->getUserDetails($userId);
            return $userDetails;
        }catch (\Exception $e){
            throw new \Exception("User not found for $userId    ");
        }
    }
    public function all()
    {
        return $this->UserRepository->all();
    }

    public function find($id)
    {
        return $this->UserRepository->find($id);
    }


    public function create(array $data)
    {
        try {
            // Generate a random OTP
            $data['otp'] = rand(100000, 999999);

            // Create the user
            $user = $this->UserRepository->create($data);

            // Send OTP via email
            Mail::to($user->email)->send(new OtpMail($user->otp));

            // Return the created user
            return $user;
        } catch (\Exception $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }


    public function update($id, array $data)
    {
        return $this->UserRepository->update($id, $data);
    }
    
    public function delete($id)
    {
        return $this->UserRepository->delete($id);
    }

    public function verifyOtp(array $data)
    {
        $user = $this->UserRepository->findByEmail($data['email']);

        if (!$user) {
            throw new \Exception("User not found.");
        }

        if ($user->otp != $data['otp']) {
            throw new \Exception("Invalid OTP.");
        }

        // Mark OTP as verified
        $this->UserRepository->update($user->id, [
            'email_verified_at' => now(),
            'otp_verified' => 1,
            'otp_verified_at' => now(),
            'otp' => null, // Clear OTP after successful verification
        ]);

        return $user;
    }


    public function login(array $data)
    {
        $query = User::where('email', $data['email']);

        if (isset($data['role'])) {
            $query->where('role', $data['role']);
        }

        $user = $query->first();

        if (!$user || Hash::check($data['password'], $user->password)) {
            throw new \Exception('Invalid credentials.');
        }

        // if (!$user->otp_verified) {
        //     throw new \Exception('OTP not verified.');
        // }

        return $user;
    }
    public function resendOtp(string $email)
    {
        try {
            $user = $this->UserRepository->findByEmail($email);
            if (!$user) {
                throw new \Exception("User not found.");
            }

            $user->otp = rand(100000, 999999);
            $user->save();

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($user->otp));

            return $user;
        } catch (\Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            throw new \Exception('Resend OTP failed.');
        }
    }
    public function sendPasswordResetOtp(string $email)
    {
        try {
            $user = $this->UserRepository->findByEmail($email);
            if (!$user) {
                throw new \Exception("User not found.");
            }

            $user->otp = rand(100000, 999999);
            $user->save();

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($user->otp));

            return $user;
        } catch (\Exception $e) {
            Log::error('Forgot Password OTP Error: ' . $e->getMessage());
            throw new \Exception('Could not send OTP.');
        }
    }

    public function verifyPasswordResetOtp(string $email, string $otp)
    {
        try {
            $user = $this->UserRepository->findByEmail($email);
            if (!$user) {
                throw new \Exception("User not found.");
            }

            if ($user->otp !== $otp) {
                throw new \Exception("Invalid OTP.");
            }

            // OTP is verified, reset it to avoid reuse
            $user->otp = null;
            $user->save();

            return $user;
        } catch (\Exception $e) {
            Log::error('Verify OTP Error: ' . $e->getMessage());
            throw new \Exception('OTP verification failed.');
        }
    }

    public function resetPassword(string $email, string $newPassword)
    {
        try {
            $user = $this->UserRepository->findByEmail($email);
            if (!$user) {
                throw new \Exception("User not found.");
            }

            $user->password = bcrypt($newPassword);
            $user->save();

            return $user;
        } catch (\Exception $e) {
            Log::error('Password Reset Error: ' . $e->getMessage());
            throw new \Exception('Could not reset password.');
        }
    }
    public function updateProfile($data)
    {
        $user = auth()->user();

        if (isset($data['profile_picture'])) {
            $path = $data['profile_picture']->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);
        return $user;
    }
}
