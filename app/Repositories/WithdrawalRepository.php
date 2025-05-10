<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;

class WithdrawalRepository
{
    public function all()
    {
        return Withdrawal::all();
    }

    public function find($id)
    {
        return Withdrawal::find($id);
    }

    public function create(array $data)
    {
        $withdrawal = Withdrawal::create($data);
        //cut balance from wallet
        $wallet = Wallet::where('user_id', $data['user_id'])->first();
        $wallet->balance -= $data['amount'];
        $wallet->save();
        //create a transaction record
        $transaction = new Transaction();
        $transaction->user_id = $data['user_id'];
        $transaction->amount = $data['amount'];
        $transaction->transaction_type = 'withdrawal';
        $transaction->status = 'completed';
        $transaction->reference = 'Withdrawal_' . time();
        $transaction->save();
        return $withdrawal;
    }

    public function update($id, array $data)
    {
        return Withdrawal::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Withdrawal::destroy($id);
    }
}
