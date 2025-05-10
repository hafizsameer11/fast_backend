<?php

// use App\Http\Controllers\Rider\AuthController;

use App\Http\Controllers\Admin\UsermanagementController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\ParcelBidController;
use App\Http\Controllers\ParcelReviewController;
use App\Http\Controllers\Rider\RiderVerificationController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Rider\AuthController as RiderAuthController;
use App\Http\Controllers\Rider\DistanceController;
use App\Http\Controllers\Rider\NearbyParcelController;
use App\Http\Controllers\Rider\RiderLocationController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\SendParcelController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\ChatController;
use App\Http\Controllers\User\FaqController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

Route::get('/migrate', function () {
    Artisan::call('migrate');
    return response()->json(['message' => 'Migration successful'], 200);
});
Route::get('/migrate/rollback', function () {
    Artisan::call('migrate:rollback');
    return response()->json(['message' => 'Migration rollback successfully'], 200);
});

Route::get('/migrate/fresh', function () { // migrate:fresh
    Artisan::call('migrate:fresh');
    return response()->json(['message' => 'Migration fresh successfully'], 200);
});


Route::get('/unatuh', function () {
    return response()->json("unauthorized", 401);
})->name('login');


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

Route::prefix('auth/rider/verification')->middleware('auth:sanctum')->group(function () {
    Route::post('step-1', [RiderVerificationController::class, 'step1']);
    Route::post('step-2', [RiderVerificationController::class, 'step2']);
    Route::post('step-3', [RiderVerificationController::class, 'step3']);
});


Route::middleware('auth:sanctum')->prefix('address')->group(function () {
    Route::post('create', [AddressController::class, 'create']);
    Route::get('list', [AddressController::class, 'index']);
    Route::put('update/{id}', [AddressController::class, 'update']);
    Route::delete('delete/{id}', [AddressController::class, 'destroy']);
});


Route::prefix('sendparcel')->middleware('auth:sanctum')->group(function () {

    Route::post('create-step-one', [SendParcelController::class, 'createStepOne']);
    Route::post('{id}/step-two', [SendParcelController::class, 'stepTwo']);
    Route::post('{id}/step-three', [SendParcelController::class, 'stepThree']);
    Route::post('{id}/step-four', [SendParcelController::class, 'stepFour']);
    Route::get('list', action: [SendParcelController::class, 'index']);
    Route::put('{id}/status', [SendParcelController::class, 'updateStatus']); // ✅ new route
    Route::post('{id}/confirm-pickup', [SendParcelController::class, 'confirmPickup']);
    Route::post('{id}/confirm-delivery', [SendParcelController::class, 'confirmDelivery']);
    Route::get('{id}/cancel', [SendParcelController::class, 'cancelParcel']);
});


Route::prefix('parcel-bid')->middleware('auth:sanctum')->group(function () {

    Route::post('create', [ParcelBidController::class, 'store']); // rider


    Route::post('create-by-user', [ParcelBidController::class, 'storeByUser']); // user

    Route::get('{parcelId}/list', [ParcelBidController::class, 'list']); // both

    Route::put('accept/{bidId}', [ParcelBidController::class, 'accept']); // user accepts rider bid

    Route::put('rider-accept/{bidId}', [ParcelBidController::class, 'riderAccept']); // rider accepts user bid

});

Route::prefix('rider/location')->middleware('auth:sanctum')->group(function () {
    Route::post('update', [RiderLocationController::class, 'updateLocation']);
    Route::get('{riderId}', [RiderLocationController::class, 'getRiderLocation']);
});
Route::post('rider/check-proximity', [DistanceController::class, 'check']);

Route::middleware('auth:sanctum')->prefix('rider')->group(function () {
    Route::post('nearby-parcels', [NearbyParcelController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('track')->group(function () {
    Route::get('user/{parcelId}', [TrackController::class, 'userViewRiderLocation']);
    Route::get('rider/route/{parcelId}', [TrackController::class, 'riderRouteToDelivery']);
});

Route::prefix('parcel-review')->middleware('auth:sanctum')->group(function () {
    Route::post('submit', [ParcelReviewController::class, 'submit']);
});



Route::middleware('auth:sanctum')->prefix('sendparcel')->group(function () {
    Route::post('{id}/update-location', [SendParcelController::class, 'updateLocation']);
});



Route::middleware('auth:sanctum')->prefix('history')->group(function () {
    Route::get('rider', [HistoryController::class, 'riderHistory']);
    Route::get('user', [HistoryController::class, 'userHistory']);
});


Route::prefix('withdrawal')->group(function () {
    Route::post('store', [WithdrawalController::class, 'store']);
    Route::get('list', [WithdrawalController::class, 'index']);
});


Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::post('send', [ChatController::class, 'send']);
    Route::get('messages/{userId}', [ChatController::class, 'getMessagesWithUser']);
    Route::get('inbox', [ChatController::class, 'inbox']);
    Route::get('connected-users', [ChatController::class, 'connectedUsers']); // 🔥 new
    Route::get('connected-riders', [ChatController::class, 'connectRiders']); // 🔥 new


    // ✅ Support chat routes
    Route::post('support/send', [ChatController::class, 'sendSupport']); // user/rider to admin
    Route::get('support/messages', [ChatController::class, 'supportMessages']); // get support history
    Route::post('support/reply/{messageId}', [ChatController::class, 'adminReply']); // admin replies
});


Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
});


Route::prefix('faqs')->middleware('auth:sanctum')->group(function () {
    Route::get('/user', [FaqController::class, 'userFaqs']);
    Route::get('/rider', [FaqController::class, 'riderFaqs']);

    // ✅ Admin routes for managing FAQs
    Route::post('/create', [FaqController::class, 'store']);
    Route::put('/update/{id}', [FaqController::class, 'update']);
    Route::delete('/delete/{id}', [FaqController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('usermanagement', [UsermanagementController::class, 'getUserManagment']);
        Route::get('usermanagement/get-user-details/{userId}', [UsermanagementController::class, 'getUserDetails']);
        Route::post('usermanagement/edit-user/{userId}', [UsermanagementController::class, 'editUser']);
        Route::get('usermanagement/get-parcel-for-user/{userId}', [UsermanagementController::class, 'getParcelForUser']);
        Route::get('usermanagement/get-parcel-details/{parcelId}', [UsermanagementController::class, 'getParcelDetails']);
        Route::get('usermanagement/get-user-chats/{userId}', [UsermanagementController::class, 'getUserChats']);
        Route::get('usermanagement/get-conversation-between-users/{userId}/{receiverId}', [UsermanagementController::class, 'getConversationBetweenUsers']);
    });
});
