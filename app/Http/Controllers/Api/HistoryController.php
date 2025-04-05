<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParcelHistoryService;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class HistoryController extends Controller
{
    protected $parcelHistoryService;

    public function __construct(ParcelHistoryService $parcelHistoryService)
    {
        $this->parcelHistoryService = $parcelHistoryService;
    }

    public function riderHistory(Request $request)
    {
        $riderId = auth()->id();
        $data = $this->parcelHistoryService->getRiderHistory($riderId);
        return ResponseHelper::success($data, "Rider history fetched.");
    }

    public function userHistory(Request $request)
    {
        $userId = auth()->id();
        $data = $this->parcelHistoryService->getUserHistory($userId);
        return ResponseHelper::success($data, "User history fetched.");
    }
}
