<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackAnonymousVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => [
                'nullable',
                'string',
                Rule::in(['visit', 'itsme', 'id', 'code', 'terms']),
            ],
            'locale' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function eventType(): string
    {
        return (string) ($this->validated('event') ?? 'visit');
    }

    public function normalizedLocale(): string
    {
        $raw = strtolower(substr((string) ($this->validated('locale') ?? ''), 0, 2));

        return in_array($raw, ['nl', 'fr'], true) ? $raw : 'nl';
    }
}
