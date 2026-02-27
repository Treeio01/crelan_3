<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnlineStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'is_online' => 'required|boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'is_online.required' => 'Online status is required',
            'is_online.boolean' => 'Online status must be true or false',
        ];
    }
}
