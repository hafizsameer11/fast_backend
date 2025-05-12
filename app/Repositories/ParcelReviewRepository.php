<?php

namespace App\Repositories;

use App\Models\ParcelReview;
use App\Models\SendParcel;
use Illuminate\Support\Facades\Auth;

class ParcelReviewRepository
{
    public function create(array $data)
    {
        $user = Auth::user();
        $parcel = SendParcel::find($data['send_parcel_id']);
        if ($user->role == 'user') {
            $data['to_user_id'] = $parcel->rider_id;
        } else {
            $data['to_user_id'] = $parcel->user_id;
        }
        //chek if already reveeiw with this to user and parcel exsts
        $review = ParcelReview::where('to_user_id', $data['to_user_id'])
            ->where('send_parcel_id', $data['send_parcel_id'])
            ->first();
            if ($review) {
                throw new \Exception("Review already exists");
                // return $review;
            }
        return ParcelReview::create($data);
    }

    public function getForUser($userId)
    {
        return ParcelReview::where('to_user_id', $userId)
            ->with('fromUser')
            ->latest()
            ->get();
    }
}
