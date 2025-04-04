<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiderVerificationStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passport_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'rider_permit_upload' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
            'vehicle_video' => 'required|mimes:mp4,mov,avi|max:10240', // 10MB max
        ];
    }
}
