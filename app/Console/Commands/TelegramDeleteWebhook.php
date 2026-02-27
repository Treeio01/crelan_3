<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;

class TelegramDeleteWebhook extends Command
{
    protected $signature = 'telegram:webhook:delete {--drop-pending : Drop pending updates}';
    protected $description = 'Delete Telegram webhook (switch back to polling)';

    public function handle(Nutgram $bot): int
    {
        $dropPending = $this->option('drop-pending');
        
        $this->info('Deleting webhook...');
        
        try {
            $result = $bot->deleteWebhook($dropPending);
            
            if ($result) {
                $this->info('✅ Webhook deleted successfully!');
                $this->info('You can now use polling: php artisan telegram:bot');
                return self::SUCCESS;
            }
            
            $this->error('Failed to delete webhook');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
