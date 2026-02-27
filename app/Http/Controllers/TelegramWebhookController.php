<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Telegram\TelegramBot;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramBot $telegramBot,
    ) {}

    public function __invoke(): Response
    {
        Log::info('Telegram webhook received');

        try {
            $this->telegramBot->run();
        } catch (\Throwable $e) {
            Log::error('Telegram webhook error', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
        }

        return response('ok');
    }
}
