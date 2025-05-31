<?php

namespace App\Services;

use App\Repositories\ParcelReviewRepository;

class ParcelReviewService
{
    protected $parcelReviewRepository;

    public function __construct(ParcelReviewRepository $parcelReviewRepository)
    {
        $this->parcelReviewRepository = $parcelReviewRepository;
    }

    public function submitReview(array $data)
    {
        try{
            return $this->parcelReviewRepository->create($data);
        }catch(\Exception $e){
            throw $e;
        }
    }

    public function getReviewsForUser($userId)
    {
        return $this->parcelReviewRepository->getForUser($userId);
    }
}
