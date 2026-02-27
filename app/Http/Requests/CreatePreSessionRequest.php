<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'page_name' => 'required|string|max:255',
            'page_url' => 'nullable|url|max:500',
        ];
    }
    
    public function messages(): array
    {
        return [
            'page_name.required' => 'Page name is required',
            'page_name.max' => 'Page name must not exceed 255 characters',
            'page_url.url' => 'Page URL must be a valid URL',
            'page_url.max' => 'Page URL must not exceed 500 characters',
        ];
    }
}
