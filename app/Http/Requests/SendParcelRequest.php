<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendParcelRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric',
            'sender_address' => 'required|string',
            'receiver_address' => 'required|string',
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'parcel_category' => 'required|string',
            'parcel_value' => 'required|numeric',
            'description' => 'nullable|string',
            'payer' => 'required|in:sender,receiver,third-party',
            'amount' => 'required|numeric',
            'delivery_fee' => 'required|numeric'
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'data' => $validator->errors(),
                'message' => $validator->errors()->first()
            ], 422)
        );
    }
}
