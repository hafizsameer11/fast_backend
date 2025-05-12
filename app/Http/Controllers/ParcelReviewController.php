<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ParcelReviewService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\SubmitParcelReviewRequest;
use Illuminate\Support\Facades\Auth;

class ParcelReviewController extends Controller
{
    protected $parcelReviewService;

    public function __construct(ParcelReviewService $parcelReviewService)
    {
        $this->parcelReviewService = $parcelReviewService;
    }

    public function submit(SubmitParcelReviewRequest $request)
    {
        $data = $request->validated();
        $data['from_user_id'] = auth()->id();
        $user=Auth::user();
        

        $review = $this->parcelReviewService->submitReview($data);

        return ResponseHelper::success($review, 'Review submitted successfully');
    }
}
