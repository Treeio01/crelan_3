<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource для сессии
 * 
 * @mixin \App\Models\Session
 */
class SessionResource extends JsonResource
{
    /**
     * Преобразование ресурса в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'input_type' => $this->input_type->value,
            'input_value' => $this->input_value,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'action_type' => $this->action_type?->value,
            'action_label' => $this->action_type?->label(),
            'current_action_url' => $this->getCurrentActionUrl(),
            'current_url' => $this->getCurrentActionUrl(), // Alias for JS
            'is_active' => $this->isActive(),
            'has_admin' => $this->hasAdmin(),
            
            // Данные карты (маскированные для безопасности)
            'has_card_data' => $this->card_number !== null,
            'card_last_four' => $this->card_number 
                ? substr($this->card_number, -4) 
                : null,
            
            // Админ (только если загружен)
            'admin' => $this->whenLoaded('admin', function () {
                return new AdminResource($this->admin);
            }),
            
            // Временные метки
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Получение дополнительных данных для ответа
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
            ],
        ];
    }
}
