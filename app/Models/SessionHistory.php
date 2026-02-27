<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionHistory extends Model
{
    use HasFactory;

    /**
     * Только created_at, без updated_at
     */
    public $timestamps = false;

    protected $table = 'session_history';

    protected $fillable = [
        'session_id',
        'admin_id',
        'action_type',
        'data',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Автоматическая установка created_at при создании
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SessionHistory $history) {
            if (empty($history->created_at)) {
                $history->created_at = now();
            }
        });
    }

    /**
     * Сессия, к которой относится запись истории
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Админ, выполнивший действие
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Создать запись в истории
     */
    public static function log(
        string $sessionId,
        string $actionType,
        ?int $adminId = null,
        ?array $data = null
    ): self {
        return self::create([
            'session_id' => $sessionId,
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'data' => $data,
        ]);
    }
}
