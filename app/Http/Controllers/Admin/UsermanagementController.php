<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditProfileRequest;
use App\Services\SendParcelService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;

class UsermanagementController extends Controller
{
    protected $userService, $sendParcelService;
    public function __construct(UserService $userService, SendParcelService $sendParcelService)
    {
        $this->userService = $userService;
        $this->sendParcelService = $sendParcelService;
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
    public function getParcelDetails($parcelId)
    {
        try {
            $data = $this->sendParcelService->find($parcelId);
            return ResponseHelper::success($data);
        } catch (Exception $e) {
            return ResponseHelper::error("Parcel not found for $parcelId");
        }
    }
}
