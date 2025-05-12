<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\WalletRepository;
use App\Services\ChatService;
use App\Services\SendParcelService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;

class RiderManagementController extends Controller
{
    protected $userService, $sendParcelService, $chatService, $walletRepository;
    public function __construct(
        UserService $userService,
        SendParcelService $sendParcelService,
        ChatService $chatService,
        WalletRepository $walletRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->userService = $userService;
        $this->sendParcelService = $sendParcelService;
        $this->chatService = $chatService;
    }
    public function getUserManagment()
    {
        try {
            $data = $this->userService->getRiderManagement();
            // return view('admin.usermanagement', compact('users'));
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found");
        }
    }
    public function getRiderDetails($userId)
    {
        try {

            $userDetails = $this->userService->getRiderDetails($userId);
            return ResponseHelper::success($userDetails);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId $e");
        }
    }
    public function getParcelForRider($userId)
    {
        try {
            $data = $this->sendParcelService->getParcelForRider($userId);
            // $data = $this->userService->getParcelForUser($userId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            // log errors
            \Log::error("Error fetching parcels for user $userId: " . $e->getMessage());
            return ResponseHelper::error("User not found for $userId");
        }
    }
    public function   getParcelDetails($parcelId)
    {
        try {
            $data = $this->sendParcelService->find($parcelId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("Parcel not found for $parcelId");
        }
    }
    public function getUserChats($userId)
    {
        try {
            $data = $this->chatService->getUsersConnectedToRider($userId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId");
        }
    }

    public function getConversationBetweenUsers($userId, $receiverId)
    {
        try {
            $data = $this->chatService->getConversationBetweenUsers($userId, $receiverId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId");
        }
    }
    public function getUserTransactions($userId)
    {
        try {
            $data = $this->walletRepository->getTransactionData($userId);
            $transactions = $this->walletRepository->getTransactionHistory($userId);
            $sendingData = [
                'stats' => $data,
                'transactions' => $transactions
            ];
            return ResponseHelper::success($sendingData);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId");
        }
    }
    //
}
