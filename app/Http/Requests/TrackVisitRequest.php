<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_name' => ['nullable', 'string', 'max:255'],
            'page_url' => ['nullable', 'url', 'max:2048'],
            'action_type' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function pageName(): string
    {
        return (string) ($this->validated('page_name') ?? 'Неизвестная страница');
    }

    public function pageUrl(): string
    {
        return (string) ($this->validated('page_url') ?? $this->fullUrl());
    }

    public function actionType(): ?string
    {
        $value = $this->validated('action_type');

        return $value === null ? null : (string) $value;
    }
}
