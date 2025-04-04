<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdressService;
use App\Http\Requests\AddressRequest;
use App\Helpers\ResponseHelper;

class AddressController extends Controller
{
    protected $AddressService;

    public function __construct(AdressService $AddressService)
    {
        $this->AddressService = $AddressService;
    }

    public function create(AddressRequest $request)
    {
        try {
            $address = $this->AddressService->create($request->validated());
            return ResponseHelper::success($address, "Address created successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }

    public function index()
    {
        try {
            $addresses = $this->AddressService->all();
            return ResponseHelper::success($addresses, "Address list retrieved successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }

    public function update(AddressRequest $request, $id)
    {
        try {
            $address = $this->AddressService->update($id, $request->validated());
            return ResponseHelper::success($address, "Address updated successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->AddressService->delete($id);
            return ResponseHelper::success($deleted, "Address deleted successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }
}
