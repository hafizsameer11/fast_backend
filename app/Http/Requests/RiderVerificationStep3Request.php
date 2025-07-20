<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RiderVerificationStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passport_photo' => 'required|image',
            'rider_permit_upload' => 'required',
            'vehicle_video' => 'required', // 10MB max
        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'data' => $validator->errors(),
            'message' => $validator->errors()->first()
        ], 422));
    }

}
