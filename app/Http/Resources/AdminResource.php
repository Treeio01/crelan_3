<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource для админа
 * 
 * @mixin \App\Models\Admin
 */
class AdminResource extends JsonResource
{
    /**
     * Преобразование ресурса в массив
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'telegram_user_id' => $this->telegram_user_id,
            'username' => $this->username,
            'display_name' => $this->username ?? 'Admin #' . $this->id,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'is_active' => $this->is_active,
            'is_super_admin' => $this->isSuperAdmin(),
            
            // Статистика (если загружена)
            'sessions_count' => $this->whenCounted('sessions'),
            'completed_sessions_count' => $this->completed_sessions_count,
            'active_sessions_count' => $this->active_sessions_count,
            
            // Временные метки
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
