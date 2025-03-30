<?php

namespace App\Http\Controllers\User;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
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
            $user = $this->UserService->create($request->validated());
            return ResponseHelper::success($user, "User created successfully");
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}
