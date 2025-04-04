<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiderVerificationStep2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_type' => 'required|string',
            'plate_number' => 'required|string',
            'riders_permit_number' => 'required|string',
            'color'=> 'required|string',

        ];
    }
}
