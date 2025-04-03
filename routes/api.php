<?php

// use App\Http\Controllers\Rider\AuthController;

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Rider\AuthController as RiderAuthController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\SendParcelController;
use App\Http\Controllers\User\WithdrawalController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/optimize-app', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('optimize');

    return "Application optimized and caches cleared successfully!";
});

Route::prefix("auth/user")->group(function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post('/otp-verification', [AuthController::class, 'otpVerification']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/forget-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-forget-password-otp', [AuthController::class, 'verifyForgetPasswordOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);



});

Route::prefix("auth/rider")->group(function () {
    Route::post("register", [RiderAuthController::class, "register"]);
    Route::post("otp-verification", [RiderAuthController::class, "otpVerification"]);
    Route::post("login", [RiderAuthController::class, "login"]);
    Route::post("resend-otp", [RiderAuthController::class, "resendOtp"]);
    Route::post("forget-password", [RiderAuthController::class, "forgotPassword"]);
    Route::post("verify-forget-password-otp", [RiderAuthController::class, "verifyForgetPasswordOtp"]);
    Route::post("reset-password", [RiderAuthController::class, "resetPassword"]);
});


Route::prefix('address')->group(function () {
    Route::post('create', [AddressController::class, 'create']);
    Route::get('list', [AddressController::class, 'index']); // New route to get the address list
});


Route::prefix('sendparcel')->group(function () {
    Route::post('create', [SendParcelController::class, 'create']);
    Route::get('list', action: [SendParcelController::class, 'index']);
    Route::put('{id}/status', [SendParcelController::class, 'updateStatus']); // ✅ new route
});




Route::prefix('withdrawal')->group(function () {
    Route::post('store', [WithdrawalController::class, 'store']);
    Route::get('list', [WithdrawalController::class, 'index']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
