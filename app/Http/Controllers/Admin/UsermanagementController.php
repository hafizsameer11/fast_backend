<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditProfileRequest;
use App\Repositories\WalletRepository;
use App\Services\ChatService;
use App\Services\SendParcelService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;

class UsermanagementController extends Controller
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
            $data = $this->userService->usermanagement();
            // return view('admin.usermanagement', compact('users'));
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found");
        }
    }
    public function getUserDetails($userId)
    {
        try {
            $userDetails = $this->userService->getUserDetails($userId);
            return ResponseHelper::success($userDetails);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId $e");
        }
    }
    public function editUser($userId, EditProfileRequest $request)
    {
        try {
            $data = $this->userService->update($userId, $request->validated());
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("User not found for $userId");
        }
    }
    public function getParcelForUser($userId)
    {
        try {
            $data = $this->sendParcelService->getParcelForUser($userId);
            // $data = $this->userService->getParcelForUser($userId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
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
            $data = $this->chatService->getRidersConnectedToUser($userId);
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
}
