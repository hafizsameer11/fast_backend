<?php

namespace App\Repositories;

use App\Models\ParcelReview;

class ParcelReviewRepository
{
    public function create(array $data)
    {
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
