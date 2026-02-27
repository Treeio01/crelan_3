<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\InputType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for session creation request
 */
class CreateSessionRequest extends FormRequest
{
    /**
     * Authorize the request
     */
    public function authorize(): bool
    {
        return true; // Публичный endpoint
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'input_type' => [
                'required',
                'string',
                Rule::in(InputType::values()),
            ],
            'input_value' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'input_type.required' => 'Input type is required',
            'input_type.in' => 'Invalid input type',
            'input_value.required' => 'Input value is required',
            'input_value.min' => 'Input value is too short',
            'input_value.max' => 'Input value is too long',
        ];
    }

    /**
     * Get InputType enum
     */
    public function getInputType(): InputType
    {
        return InputType::from($this->validated('input_type'));
    }

    /**
     * Get input value
     */
    public function getInputValue(): string
    {
        return $this->validated('input_value');
    }
}
