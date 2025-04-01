<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Services\WithdrawalService;
use App\Helpers\ResponseHelper;

class WithdrawalController extends Controller
{
    protected $withdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    public function store(WithdrawalRequest $request)
    {
        try {
            $withdrawal = $this->withdrawalService->create($request->validated());
            return ResponseHelper::success($withdrawal, "Withdrawal request created successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->withdrawalService->getAll()
        ]);
    }
}
