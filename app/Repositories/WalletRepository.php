<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\VirtualAccount;
use App\Models\Wallet;

class WalletRepository
{
    public function all()
    {
        // Add logic to fetch all data
    }
    public function generateVirtualAccount($userId)
    {
        $refference = 'Fast_Logistics_' . time();
        $accountNumber = '1234567890'; // Replace with actual logic to generate account number
        $accountName = 'Fast Logistics'; // Replace with actual logic to generate account name
        $virtualAccount = new VirtualAccount();
        $virtualAccount->account_number = $accountNumber;
        $virtualAccount->account_name = $accountName;
        $virtualAccount->user_id = $userId;
        $virtualAccount->reference = $refference;
        $virtualAccount->save();
        return $virtualAccount;
    }
    public function madePayment($userId, $amount)
    {
        // Add logic to handle payment
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$wallet) {
            $wallet = new Wallet();
            $wallet->user_id = $userId;
            $wallet->balance = 0;
            $wallet->save();
        }
        $wallet->balance += $amount;
        $wallet->save();
        $transaction = new Transaction();
        $transaction->user_id = $userId;
        $transaction->amount = $amount;
        $transaction->transaction_type = 'topup';
        $transaction->status = 'completed';
        $transaction->reference = 'Payment_' . time();
        $transaction->save();
        return $transaction;
    }
    public function getWalletBalance($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        if ($wallet) {
            return $wallet->balance;
        } else {
            //crteate a new wallet for the user if it doesn't exist
            $wallet = new Wallet();
            $wallet->user_id = $userId;
            $wallet->balance = 0;
            $wallet->save();
            return $wallet->balance;
        }
        // return 0;
    }
    public function getTransactionHistory($userId)
    {
        return Transaction::where('user_id', $userId)->get();
    }
    public function getVirtualAccount($userId)
    {
        return VirtualAccount::where('user_id', $userId)->latest()->first();
    }

    public function find($id)
    {
        // Add logic to find data by ID
    }

    public function create(array $data)
    {
        // Add logic to create data
    }

    public function update($id, array $data)
    {
        // Add logic to update data
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
}
