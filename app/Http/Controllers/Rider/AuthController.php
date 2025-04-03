<?php

namespace App\Http\Controllers\Rider;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
  protected $UserService;

  public function __construct(UserService $UserService)
  {
    $this->UserService = $UserService;
  }

  public function register(RegisterRequest $request)
  {
    try {
      $data = $request->validated();
      $data['role'] = 'rider';

      $user = $this->UserService->create($data);
      return ResponseHelper::success($user, "Rider registered successfully");
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }


  public function otpVerification(OtpVerificationRequest $request)
  {
    try {
      $validated = $request->validated();
      $validated['role'] = 'rider';

      $user = $this->UserService->verifyOtp($validated);
      return ResponseHelper::success($user, 'OTP verified successfully', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function login(LoginRequest $request)
  {
    try {
      $validated = $request->validated();
      $validated['role'] = 'rider';

      $user = $this->UserService->login($validated);
      $token = $user->createToken('auth_token')->plainTextToken;

      $data = [
        'user' => $user,
        'token' => $token
      ];

      return ResponseHelper::success($data, 'Rider logged in successfully', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function resendOtp(Request $request)
  {
    try {
      $request->validate([
        'email' => 'required|email|exists:users,email'
      ]);

      $user = $this->UserService->resendOtp($request->email, 'rider');
      return ResponseHelper::success($user, 'OTP resent successfully', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function forgotPassword(Request $request)
  {
    try {
      $request->validate([
        'email' => 'required|email|exists:users,email'
      ]);

      $user = $this->UserService->sendPasswordResetOtp($request->email, 'rider');
      return ResponseHelper::success($user, 'OTP sent to your email', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function verifyForgetPasswordOtp(Request $request)
  {
    try {
      $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|numeric'
      ]);

      $user = $this->UserService->verifyPasswordResetOtp($request->email, $request->otp, 'rider');
      return ResponseHelper::success($user, 'OTP verified successfully', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function resetPassword(Request $request)
  {
    try {
      $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:6|confirmed'
      ]);

      $user = $this->UserService->resetPassword($request->email, $request->password, 'rider');
      return ResponseHelper::success($user, 'Password reset successfully', 200);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }
}
