<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'zone_id',
        'ip_address',
        'nameservers',
        'ssl_mode',
        'status',
        'admin_id',
        'is_active',
    ];

    protected $casts = [
        'nameservers' => 'array',
        'is_active' => 'boolean',
        'admin_id' => 'integer',
    ];

    /**
     * Админ, который добавил домен
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Получить NS записи в виде строки
     */
    public function getNameserversString(): string
    {
        if (empty($this->nameservers) || !is_array($this->nameservers)) {
            return 'Не указаны';
        }

        return implode("\n", $this->nameservers);
    }
}
