<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlockedIp;
use Illuminate\Console\Command;

/**
 * Команда для управления заблокированными IP адресами
 */
class ManageBlockedIpsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'blocked-ip:manage
                            {action? : Действие: list, unblock}
                            {ip? : IP адрес для разблокировки}';

    /**
     * The console command description.
     */
    protected $description = 'Управление заблокированными IP адресами';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action') ?? 'list';
        
        return match($action) {
            'list' => $this->listBlockedIps(),
            'unblock' => $this->unblockIp(),
            default => $this->showHelp(),
        };
    }

    /**
     * Показать список заблокированных IP
     */
    private function listBlockedIps(): int
    {
        $blockedIps = BlockedIp::with('blockedBy')
            ->orderBy('blocked_at', 'desc')
            ->get();

        if ($blockedIps->isEmpty()) {
            $this->info('📋 Нет заблокированных IP адресов');
            return self::SUCCESS;
        }

        $this->info('🚫 Заблокированные IP адреса:');
        $this->newLine();

        $rows = [];
        foreach ($blockedIps as $blocked) {
            $rows[] = [
                $blocked->ip_address,
                $blocked->blockedBy?->username ?? 'N/A',
                $blocked->reason ?? '-',
                $blocked->blocked_at->format('Y-m-d H:i:s'),
            ];
        }

        $this->table(
            ['IP адрес', 'Заблокировал', 'Причина', 'Дата блокировки'],
            $rows
        );

        $this->newLine();
        $this->info("Всего заблокировано: {$blockedIps->count()}");
        $this->newLine();
        $this->comment('Для разблокировки используйте: php artisan blocked-ip:manage unblock <IP>');

        return self::SUCCESS;
    }

    /**
     * Разблокировать IP адрес
     */
    private function unblockIp(): int
    {
        $ip = $this->argument('ip');

        if (empty($ip)) {
            $this->error('❌ Укажите IP адрес для разблокировки');
            $this->comment('Пример: php artisan blocked-ip:manage unblock 192.168.1.100');
            return self::FAILURE;
        }

        // Проверяем, заблокирован ли IP
        if (!BlockedIp::isBlocked($ip)) {
            $this->warn("⚠️  IP адрес {$ip} не заблокирован");
            return self::FAILURE;
        }

        // Получаем информацию о блокировке
        $blocked = BlockedIp::where('ip_address', $ip)->first();
        
        if (!$blocked) {
            $this->error("❌ Не удалось найти запись о блокировке");
            return self::FAILURE;
        }

        // Показываем информацию
        $this->info("📋 Информация о блокировке:");
        $this->table(
            ['IP', 'Заблокировал', 'Причина', 'Дата'],
            [[
                $blocked->ip_address,
                $blocked->blockedBy?->username ?? 'N/A',
                $blocked->reason ?? '-',
                $blocked->blocked_at->format('Y-m-d H:i:s'),
            ]]
        );
        $this->newLine();

        // Запрашиваем подтверждение
        if (!$this->confirm("Вы уверены, что хотите разблокировать IP {$ip}?", true)) {
            $this->info('❌ Отменено');
            return self::SUCCESS;
        }

        // Разблокируем
        if (BlockedIp::unblock($ip)) {
            $this->info("✅ IP адрес {$ip} успешно разблокирован");
            return self::SUCCESS;
        }

        $this->error("❌ Не удалось разблокировать IP {$ip}");
        return self::FAILURE;
    }

    /**
     * Показать помощь
     */
    private function showHelp(): int
    {
        $this->info('📖 Использование команды:');
        $this->newLine();
        $this->line('  Просмотр всех заблокированных IP:');
        $this->comment('    php artisan blocked-ip:manage');
        $this->comment('    php artisan blocked-ip:manage list');
        $this->newLine();
        $this->line('  Разблокировка конкретного IP:');
        $this->comment('    php artisan blocked-ip:manage unblock <IP>');
        $this->comment('    php artisan blocked-ip:manage unblock 192.168.1.100');
        $this->newLine();

        return self::SUCCESS;
    }
}
