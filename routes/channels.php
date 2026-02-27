<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\Session;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Каналы для WebSocket broadcasting через Laravel Reverb
|
*/

/**
 * Канал сессии - публичный канал для конкретной сессии
 * Используется для отправки событий пользователю (редиректы, статус)
 * 
 * Пользователь подключается по session_id из localStorage
 * Авторизация не требуется - канал публичный
 */
// Публичные каналы не требуют авторизации в channels.php
// session.{sessionId} - публичный канал

/**
 * Канал для всех админов - приватный
 * Используется для broadcast уведомлений о новых сессиях
 */
Broadcast::channel('admin', function ($user) {
    // Для приватного канала админов проверяем через API token или telegram_user_id
    // В нашем случае админы работают через Telegram, поэтому этот канал
    // используется только для server-side broadcasting
    // Авторизация через middleware на стороне клиента не требуется
    return true;
});

/**
 * Канал для конкретного админа - приватный
 * Используется для личных уведомлений админу
 */
Broadcast::channel('admin.{adminId}', function ($user, int $adminId) {
    // Проверка что пользователь является этим админом
    // В нашем случае админы работают через Telegram
    // Авторизация происходит через telegram_user_id
    if ($user instanceof Admin) {
        return $user->id === $adminId;
    }
    
    return false;
});

/**
 * Канал сессии - для авторизации если нужна приватность
 * Пользователь может подключиться только к своей сессии
 */
Broadcast::channel('session.{sessionId}', function ($user, string $sessionId) {
    // Публичный канал - разрешаем всем
    // Безопасность обеспечивается тем, что session_id - это UUID
    // который знает только пользователь (хранится в localStorage)
    return true;
});
