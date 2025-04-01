<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SendParcelService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SendParcelRequest;


class SendParcelController extends Controller {
    protected $sendParcelService;

    public function __construct(SendParcelService $sendParcelService) {
        $this->sendParcelService = $sendParcelService;
    }

    public function create(SendParcelRequest $request) {
        try {
            $parcel = $this->sendParcelService->create($request->validated());
            return ResponseHelper::success($parcel, "Parcel sent successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
}
