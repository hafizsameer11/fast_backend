<?php

namespace App\Repositories;
use App\Models\Address;
class AddressRepository
{
    public function all()
    {
        return Address::all(); // Fetches all addresses from the database
    }

    public function find($id)
    {
        // Add logic to find data by ID
    }

    public function create(array $data)
    {
        $address = Address::create($data);
        return $address;
    }

    public function update($id, array $data)
    {
        $address = Address::findOrFail($id);
        $address->update($data);
        return $address;
    }

    public function delete($id)
    {
        $address = Address::findOrFail($id);
        $address->delete();
        return ['deleted' => true];
    }
}