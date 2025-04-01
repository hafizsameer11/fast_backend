<?php

// use App\Http\Controllers\Rider\AuthController;

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\Rider\AuthController as RiderAuthController;
use App\Http\Controllers\User\AddressController;

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
});
Route::prefix("auth/rider")->group(function () {
    Route::post("register", [RiderAuthController::class, "registerRider"]);
});
Route::prefix('address')->group(function () {
    Route::post('create', [AddressController::class, 'create']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
