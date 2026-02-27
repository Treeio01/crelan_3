<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:webhook:set {--url= : Custom webhook URL}';
    protected $description = 'Set Telegram webhook URL';

    public function handle(Nutgram $bot): int
    {
        $url = $this->option('url') ?: url('/api/telegram/webhook');
        
        $this->info("Setting webhook to: {$url}");
        
        try {
            $result = $bot->setWebhook(
                url: $url,
                allowed_updates: ['message', 'callback_query'],
                drop_pending_updates: true,
            );
            
            if ($result) {
                $this->info('✅ Webhook set successfully!');
                
                // Показываем информацию о webhook
                $info = $bot->getWebhookInfo();
                $this->table(['Property', 'Value'], [
                    ['URL', $info->url],
                    ['Pending updates', $info->pending_update_count],
                    ['Last error', $info->last_error_message ?: 'None'],
                ]);
                
                return self::SUCCESS;
            }
            
            $this->error('Failed to set webhook');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
