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
      $this->AddressService= $AddressService;
   }
   public function create(AddressRequest $request){
    try {
        $address = $this->AddressService->create($request->validated());
        return ResponseHelper::success($adress, "Address created successfully");
    } catch (\Throwable $th) {
        return ResponseHelper::error($th->getMessage(), $th->getCode());
    }
   }
}
