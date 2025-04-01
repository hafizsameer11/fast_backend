<?php

namespace App\Http\Controllers\Rider;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
  protected $UserService;
  public function __construct(UserService $UserService){
    $this->UserService = $UserService;
  }
  public function registerRider(RegisterRequest $request){
    try{
        $data=$request->validated();
        $data['role']='rider';
        $user=$this->UserService->create($data);
        return ResponseHelper::success($user,"User created successfully");
    }catch(\Exception $e){
        return ResponseHelper::error($e->getMessage());
    }
  }
}
