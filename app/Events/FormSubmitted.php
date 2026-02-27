<?php

declare(strict_types=1);

namespace App\Events;

use App\DTOs\FormDataDTO;
use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие отправки формы пользователем
 */
class FormSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
        public readonly FormDataDTO $formData,
    ) {}
}
