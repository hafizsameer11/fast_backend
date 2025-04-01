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
        $address=Address::create($data);
        return $address;
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