<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\InputType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertPreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'input_type' => ['required', 'string', Rule::in(InputType::values())],
            'input_value' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    /**
     * @return array{input_type:string,input_value:string}
     */
    public function sessionPayload(): array
    {
        /** @var array{input_type:string,input_value:string} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
