<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;

class TelegramWebhookInfo extends Command
{
    protected $signature = 'telegram:webhook:info';
    protected $description = 'Show Telegram webhook info';

    public function handle(Nutgram $bot): int
    {
        try {
            $info = $bot->getWebhookInfo();
            
            $this->table(['Property', 'Value'], [
                ['URL', $info->url ?: '(not set - using polling)'],
                ['Pending updates', $info->pending_update_count],
                ['Max connections', $info->max_connections ?? 40],
                ['IP address', $info->ip_address ?: 'N/A'],
                ['Last error date', $info->last_error_date ? date('Y-m-d H:i:s', $info->last_error_date) : 'None'],
                ['Last error message', $info->last_error_message ?: 'None'],
                ['Allowed updates', implode(', ', $info->allowed_updates ?? []) ?: 'All'],
            ]);
            
            if ($info->url) {
                $this->info('Mode: Webhook');
            } else {
                $this->info('Mode: Polling');
            }
            
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
