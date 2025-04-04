<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiderVerificationStep1Request extends FormRequest
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email_address' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'nin_number' => 'required|string',
        ];
    }
}
