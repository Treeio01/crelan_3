<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Session;
use Illuminate\Support\Facades\Storage;

/**
 * Observer для модели Session
 * 
 * Примечание: События (SessionCreated, SessionAssigned и т.д.) диспатчатся
 * из SessionService для сохранения контекста операции.
 * Observer используется для технических операций на уровне модели.
 */
class SessionObserver
{
    /**
     * Обработка удаления сессии
     * Очистка связанных файлов
     */
    public function deleted(Session $session): void
    {
        // Удаляем папку с изображениями сессии
        $path = $session->getImagesPath();
        
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path);
        }
    }

    /**
     * Обработка принудительного удаления
     */
    public function forceDeleted(Session $session): void
    {
        $this->deleted($session);
    }
}
