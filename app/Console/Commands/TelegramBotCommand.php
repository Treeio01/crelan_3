<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Telegram\TelegramBot;
use Illuminate\Console\Command;

class TelegramBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telegram:bot';

    /**
     * The console command description.
     */
    protected $description = 'Запуск Telegram бота в режиме long polling';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBot $bot): int
    {
        $this->info('🤖 Telegram бот запущен...');
        $this->info('Нажмите Ctrl+C для остановки.');
        $this->newLine();

        $bot->run();

        return Command::SUCCESS;
    }
}
