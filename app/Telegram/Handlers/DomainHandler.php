<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Models\Admin;
use App\Models\Domain;
use App\Services\CloudflareService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для управления доменами через Cloudflare
 * 
 * Callback'и:
 * - menu:domains — меню доменов
 * - domain:add — начать добавление домена
 * - domain:list — список доменов
 * - domain:info:{domain} — информация о домене
 * - domain:edit:{domain} — редактирование IP домена
 */
class DomainHandler
{
    public function __construct(
        private readonly CloudflareService $cloudflareService,
    ) {}

    /**
     * Показать меню доменов
     * Callback: menu:domains
     */
    public function showMenu(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $domainsCount = Domain::where('is_active', true)->count();
        $activeDomains = Domain::where('is_active', true)
            ->where('status', 'active')
            ->count();

        $text = <<<TEXT
🌐 <b>Управление доменами Cloudflare</b>

📊 <b>Статистика:</b>
├ Всего доменов: <b>{$domainsCount}</b>
└ Активных: <b>{$activeDomains}</b>

Выберите действие:
TEXT;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Добавить домен', callback_data: 'domain:add'),
                InlineKeyboardButton::make('📋 Список доменов', callback_data: 'domain:list'),
            )
            ->addRow(
                InlineKeyboardButton::make('🧹 Очистить кеш', callback_data: 'domain:purge_cache'),
            )
            ->addRow(
                InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:back'),
            );

        if ($bot->callbackQuery()) {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
            $bot->answerCallbackQuery();
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
        }
    }

    /**
     * Начать добавление домена
     * Callback: domain:add
     */
    public function startAdd(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        // Сохраняем pending_action для добавления домена
        $admin->setPendingAction('domain', 'add');

        $text = <<<TEXT
➕ <b>Добавление домена</b>

Отправьте домен и IP в формате:
<code>домен IP</code>

<b>Пример:</b>
<code>example.com 192.168.1.1</code>

💡 <i>Домен будет добавлен в Cloudflare с SSL режимом Flexible</i>
TEXT;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('❌ Отмена', callback_data: 'cancel_conversation'),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Очистить кеш Cloudflare для всех активных доменов
     * Callback: domain:purge_cache
     */
    public function purgeCache(Nutgram $bot): void
    {
        $domains = Domain::where('is_active', true)
            ->whereNotNull('zone_id')
            ->get(['domain', 'zone_id']);

        if ($domains->isEmpty()) {
            $bot->answerCallbackQuery(
                text: '❌ Нет доменов с Zone ID для очистки кеша',
                show_alert: true,
            );
            return;
        }

        $success = 0;
        $failed = [];

        foreach ($domains as $domain) {
            try {
                $this->cloudflareService->purgeCache($domain->zone_id, true);
                $success++;
            } catch (\Throwable $e) {
                $failed[] = $domain->domain;
            }
        }

        $failedText = $failed ? "\n⚠️ Не удалось: " . implode(', ', $failed) : '';
        $text = "🧹 Кеш очищен для {$success} доменов." . $failedText;

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:domains')
            )
        );

        $bot->answerCallbackQuery(text: '✅ Готово');
    }

    /**
     * Список доменов
     * Callback: domain:list
     */
    public function listDomains(Nutgram $bot): void
    {
        $domains = Domain::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($domains->isEmpty()) {
            $text = "📋 <b>Список доменов пуст</b>\n\nДобавьте первый домен через кнопку \"➕ Добавить домен\"";
            
            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('➕ Добавить домен', callback_data: 'domain:add'),
                    InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:domains'),
                );

            if ($bot->callbackQuery()) {
                $bot->editMessageText(
                    text: $text,
                    parse_mode: 'HTML',
                    reply_markup: $keyboard,
                );
                $bot->answerCallbackQuery();
            } else {
                $bot->sendMessage(
                    text: $text,
                    parse_mode: 'HTML',
                    reply_markup: $keyboard,
                );
            }
            return;
        }

        $text = "📋 <b>Список доменов:</b>\n\n";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($domains as $domain) {
            $isAvailable = $this->cloudflareService->checkDomainAvailability($domain->domain);
            $statusEmoji = $isAvailable ? '✅' : '⚠️';
            
            $ipAddress = $domain->ip_address ?? 'Не указан';
            $text .= "{$statusEmoji} <code>{$domain->domain}</code>\n";
            $text .= "   └ IP: <code>{$ipAddress}</code>\n\n";

            // Добавляем кнопки для каждого домена
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "ℹ️ {$domain->domain}",
                    callback_data: "domain:info:{$domain->domain}"
                ),
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make('➕ Добавить домен', callback_data: 'domain:add'),
            InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:domains'),
        );

        if ($bot->callbackQuery()) {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
            $bot->answerCallbackQuery();
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
        }
    }

    /**
     * Информация о домене
     * Callback: domain:info:{domain}
     */
    public function infoDomain(Nutgram $bot, string $domain): void
    {
        $domainModel = Domain::where('domain', $domain)->first();

        if (!$domainModel) {
            $bot->answerCallbackQuery(
                text: "❌ Домен {$domain} не найден",
                show_alert: true,
            );
            return;
        }

        try {
            // Получаем актуальную информацию из Cloudflare
            $zoneStatus = [];
            if ($domainModel->zone_id) {
                $zoneStatus = $this->cloudflareService->getZoneStatus($domainModel->zone_id);
            }

            // Проверяем доступность
            $isAvailable = $this->cloudflareService->checkDomainAvailability($domainModel->domain);
            $statusEmoji = $isAvailable ? '✅' : '⚠️';
            $statusText = $isAvailable ? 'Работает' : 'Не доступен';

            $ipAddress = $domainModel->ip_address ?: 'Не указан';
            $text = <<<TEXT
🌐 <b>Информация о домене</b>

<b>Домен:</b> <code>{$domainModel->domain}</code>
📍 <b>IP:</b> <code>{$ipAddress}</code>
🔒 <b>SSL:</b> {$domainModel->ssl_mode}
{$statusEmoji} <b>Статус:</b> {$statusText}

<b>NS записи:</b>
<code>{$this->formatNameservers($domainModel->nameservers)}</code>
TEXT;

            if ($domainModel->admin) {
                $adminName = $domainModel->admin->username 
                    ? "@{$domainModel->admin->username}" 
                    : "ID:{$domainModel->admin->telegram_user_id}";
                $text .= "\n\n👤 <b>Добавил:</b> {$adminName}";
            }

            $text .= "\n📅 <b>Добавлен:</b> {$domainModel->created_at->format('d.m.Y H:i')}";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('✏️ Изменить IP', callback_data: "domain:edit:{$domainModel->domain}"),
                    InlineKeyboardButton::make('🔙 Назад', callback_data: 'domain:list'),
                );

            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            $bot->answerCallbackQuery();

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: "❌ Ошибка: {$e->getMessage()}",
                show_alert: true,
            );
        }
    }

    /**
     * Начать редактирование IP домена
     * Callback: domain:edit:{domain}
     */
    public function startEdit(Nutgram $bot, string $domain): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $domainModel = Domain::where('domain', $domain)->first();
        if (!$domainModel) {
            $bot->answerCallbackQuery(
                text: "❌ Домен {$domain} не найден",
                show_alert: true,
            );
            return;
        }

        // Сохраняем pending_action для редактирования домена
        $admin->setPendingAction($domain, 'edit_domain');

        $currentIp = $domainModel->ip_address ?: 'Не указан';
        $text = <<<TEXT
✏️ <b>Редактирование IP домена</b>

<b>Домен:</b> <code>{$domain}</code>
<b>Текущий IP:</b> <code>{$currentIp}</code>

Отправьте новый IP адрес:
TEXT;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('❌ Отмена', callback_data: 'cancel_conversation'),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Обработка добавления домена (из MessageHandler)
     */
    public function processAddDomain(Nutgram $bot, Admin $admin, string $input): void
    {
        $parts = explode(' ', trim($input), 2);
        
        if (count($parts) < 2) {
            $bot->sendMessage(
                text: "❌ <b>Неверный формат!</b>\n\nОтправьте домен и IP в формате:\n<code>домен IP</code>\n\nПример: <code>example.com 192.168.1.1</code>",
                parse_mode: 'HTML',
            );
            return;
        }

        $domain = trim($parts[0]);
        $ip = trim($parts[1]);

        // Валидация домена
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN) && !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i', $domain)) {
            $bot->sendMessage('❌ Неверный формат домена');
            return;
        }

        // Валидация IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $bot->sendMessage('❌ Неверный формат IP адреса');
            return;
        }

        // Проверяем, существует ли уже домен
        $existingDomain = Domain::where('domain', $domain)->first();
        if ($existingDomain) {
            $bot->sendMessage("❌ Домен <code>{$domain}</code> уже существует", parse_mode: 'HTML');
            $admin->clearPendingAction();
            return;
        }

        try {
            $bot->sendMessage("⏳ Добавляю домен <code>{$domain}</code>...", parse_mode: 'HTML');

            // Создаем зону в Cloudflare
            $zone = $this->cloudflareService->createZone($domain);
            $zoneId = $zone['id'] ?? null;

            if (!$zoneId) {
                throw new \RuntimeException('Не удалось создать зону в Cloudflare');
            }

            // Добавляем A запись
            $this->cloudflareService->setARecord($zoneId, $domain, $ip, 3600, true);

            // Устанавливаем SSL режим на flexible
            $this->cloudflareService->setSslMode($zoneId, 'flexible');

            // Получаем NS записи
            $nameservers = $this->cloudflareService->getZoneNameservers($zoneId);

            // Сохраняем в БД
            $domainModel = Domain::create([
                'domain' => $domain,
                'zone_id' => $zoneId,
                'ip_address' => $ip,
                'nameservers' => $nameservers,
                'ssl_mode' => 'flexible',
                'status' => 'active',
                'admin_id' => $admin->id,
                'is_active' => true,
            ]);

            // Проверяем доступность
            $isAvailable = $this->cloudflareService->checkDomainAvailability($domain);

            $statusEmoji = $isAvailable ? '✅' : '⚠️';
            $statusText = $isAvailable ? 'Работает' : 'Не доступен';

            $text = <<<TEXT
✅ <b>Домен добавлен!</b>

🌐 <b>Домен:</b> <code>{$domain}</code>
📍 <b>IP:</b> <code>{$ip}</code>
🔒 <b>SSL:</b> Flexible
{$statusEmoji} <b>Статус:</b> {$statusText}

<b>NS записи:</b>
<code>{$this->formatNameservers($nameservers)}</code>

💡 <i>Используйте эти NS записи для настройки домена у регистратора</i>
TEXT;

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('📋 Список доменов', callback_data: 'domain:list'),
                    InlineKeyboardButton::make('🔙 Меню', callback_data: 'menu:domains'),
                );

            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            $admin->clearPendingAction();

        } catch (\Throwable $e) {
            $bot->sendMessage(
                text: "❌ <b>Ошибка:</b> {$e->getMessage()}",
                parse_mode: 'HTML',
            );
            $admin->clearPendingAction();
        }
    }

    /**
     * Обработка редактирования IP домена (из MessageHandler)
     */
    public function processEditDomain(Nutgram $bot, Admin $admin, string $domain, string $newIp): void
    {
        // Валидация IP
        if (!filter_var($newIp, FILTER_VALIDATE_IP)) {
            $bot->sendMessage('❌ Неверный формат IP адреса');
            return;
        }

        $domainModel = Domain::where('domain', $domain)->first();
        if (!$domainModel) {
            $bot->sendMessage("❌ Домен <code>{$domain}</code> не найден", parse_mode: 'HTML');
            $admin->clearPendingAction();
            return;
        }

        if (!$domainModel->zone_id) {
            $bot->sendMessage("❌ У домена не указан Zone ID");
            $admin->clearPendingAction();
            return;
        }

        try {
            $bot->sendMessage("⏳ Обновляю IP для <code>{$domain}</code>...", parse_mode: 'HTML');

            // Обновляем A запись
            $this->cloudflareService->setARecord($domainModel->zone_id, $domain, $newIp, 3600, true);

            // Обновляем в БД
            $domainModel->update([
                'ip_address' => $newIp,
            ]);

            // Проверяем доступность
            $isAvailable = $this->cloudflareService->checkDomainAvailability($domain);
            $statusEmoji = $isAvailable ? '✅' : '⚠️';
            $statusText = $isAvailable ? 'Работает' : 'Не доступен';

            $text = <<<TEXT
✅ <b>IP обновлен!</b>

🌐 <b>Домен:</b> <code>{$domain}</code>
📍 <b>Новый IP:</b> <code>{$newIp}</code>
{$statusEmoji} <b>Статус:</b> {$statusText}
TEXT;

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('ℹ️ Информация', callback_data: "domain:info:{$domain}"),
                    InlineKeyboardButton::make('🔙 Назад', callback_data: 'domain:list'),
                );

            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            $admin->clearPendingAction();

        } catch (\Throwable $e) {
            $bot->sendMessage(
                text: "❌ <b>Ошибка:</b> {$e->getMessage()}",
                parse_mode: 'HTML',
            );
            $admin->clearPendingAction();
        }
    }

    /**
     * Форматировать NS записи
     */
    private function formatNameservers(?array $nameservers): string
    {
        if (empty($nameservers) || !is_array($nameservers)) {
            return 'Не указаны';
        }

        return implode("\n", $nameservers);
    }
}
