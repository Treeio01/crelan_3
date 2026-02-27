<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Telegram\TelegramBot;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:polling', function () {
    $this->info('Запуск Telegram бота в режиме polling (long polling)...');
    $this->warn('Для остановки нажмите Ctrl+C');
    
    $bot = new TelegramBot();
    $bot->run();
})->purpose('Run Telegram bot with long polling');
