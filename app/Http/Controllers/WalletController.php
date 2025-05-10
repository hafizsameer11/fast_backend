<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    public function all()
    {
        return $this->walletService->all();
    }
    public function find($id)
    {
        return $this->walletService->find($id);
    }
    public function madePayment(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $amount = $request->input('amount');
            $this->walletService->madePayment($userId, $amount);
            return response()->json(['message' => 'Payment successful'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function getWalletBalance()
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $balance = $this->walletService->getWalletBalance($userId);
            return response()->json(['balance' => $balance], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve balance', 'error' => $e->getMessage()], 500);
        }
    }
    public function getTransactionHistory()
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $transactions = $this->walletService->getTransactionHistory($userId);
            return response()->json(['transactions' => $transactions], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve transaction history', 'error' => $e->getMessage()], 500);
        }
    }
    public function getVirtualAccount()
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $virtualAccount = $this->walletService->getVirtualAccount($userId);
            return response()->json(['virtual_account' => $virtualAccount], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve virtual account', 'error' => $e->getMessage()], 500);
        }
    }
    public function generateVirtualAccount(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $accountName = $request->input('account_name');
            $reference = $request->input('reference');
            $virtualAccount = $this->walletService->generateVirtualAccount($userId);
            return response()->json(['virtual_account' => $virtualAccount], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate virtual account', 'error' => $e->getMessage()], 500);
        }
    }
}
