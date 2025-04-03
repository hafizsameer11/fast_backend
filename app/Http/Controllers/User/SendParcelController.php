<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateParcelStatusRequest;
use Illuminate\Http\Request;
use App\Services\SendParcelService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SendParcelRequest;


class SendParcelController extends Controller
{
    protected $sendParcelService;


    public function __construct(SendParcelService $sendParcelService)
    {
        $this->sendParcelService = $sendParcelService;
    }

    public function create(SendParcelRequest $request)
    {
        try {
            $parcel = $this->sendParcelService->create($request->validated());
            return ResponseHelper::success($parcel, "Parcel sent successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

    public function index()
    {
        try {
            $parcels = $this->sendParcelService->all();
            return ResponseHelper::success($parcels, "Parcel list retrieved successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }

    public function updateStatus(UpdateParcelStatusRequest $request, $id)
    {
        try {
            $updated = $this->sendParcelService->updateStatus($id, $request->status);
            return ResponseHelper::success($updated, "Parcel status updated successfully");
        } catch (\Throwable $th) {
            $status = $th->getCode() >= 100 && $th->getCode() <= 599 ? $th->getCode() : 500;
            return ResponseHelper::error($th->getMessage(), $status);
        }
    }
}
