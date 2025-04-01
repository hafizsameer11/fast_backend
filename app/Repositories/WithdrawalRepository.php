<?php

namespace App\Repositories;
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
        return Withdrawal::create($data);
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