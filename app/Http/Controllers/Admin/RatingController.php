<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParcelReview;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(){
        $ratings = ParcelReview::with('toUser', 'fromUser')->latest()->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Ratings retrieved successfully',
            'data' => $ratings,
        ]);
    }
}
