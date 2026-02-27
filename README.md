# Crelan Laravel Project

Проект на Laravel с поддержкой вебсокетов (Laravel Reverb) и Telegram бота (Nutgram).

## Требования

- PHP >= 8.2
- Composer
- Node.js и npm
- SQLite (или другая БД на выбор)

## Установка

1. **Клонируйте репозиторий и установите зависимости:**

```bash
composer install
npm install
```

2. **Настройте окружение:**

```bash
cp .env.example .env
php artisan key:generate
```

3. **Отредактируйте `.env` файл:**

Добавьте необходимые настройки:
- `TELEGRAM_BOT_TOKEN` - токен вашего Telegram бота (получить у @BotFather)
- Сгенерируйте ключи Reverb (команда уже встроена в Laravel Reverb):
  ```bash
  php artisan reverb:keys
  ```
  Скопируйте сгенерированные ключи в `.env` файл

4. **Создайте базу данных:**

```bash
touch database/database.sqlite
php artisan migrate
```

5. **Соберите фронтенд:**

```bash
npm run build
```

## Запуск проекта

### Разработка

Для запуска всех сервисов одновременно используйте:

```bash
composer run dev
```

Эта команда запустит:
- Laravel сервер (http://localhost:8000)
- Очередь задач (queue:listen)
- Логи (pail)
- Vite dev server

### Отдельные команды

**Laravel сервер:**
```bash
php artisan serve
```

**Laravel Reverb (WebSockets):**
```bash
php artisan reverb:start
```

**Telegram бот (polling):**
```bash
php artisan telegram:polling
```

**Очередь задач:**
```bash
php artisan queue:work
```

**Миграции:**
```bash
php artisan migrate
```

## Настройка Telegram бота

1. Создайте бота через [@BotFather](https://t.me/BotFather)
2. Получите токен бота
3. Добавьте токен в `.env` файл:
   ```
   TELEGRAM_BOT_TOKEN=your_bot_token_here
   ```

### Режим работы

**Long Polling (по умолчанию)** - для разработки и тестирования:
```bash
php artisan telegram:polling
```
Бот будет постоянно опрашивать сервер Telegram на наличие новых сообщений. Для остановки нажмите `Ctrl+C`.

**Webhook** - для продакшена (требует публичный HTTPS URL):
Если нужно использовать webhook, измените метод `run()` в `app/Telegram/TelegramBot.php`:
```php
public function run(): void
{
    $this->bot->run(); // вместо longPolling()
}
```

## Настройка WebSockets (Reverb)

Reverb настроен для работы на `localhost:8080` по умолчанию.

Для подключения на фронтенде используйте Laravel Echo:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

## Структура проекта

- `app/Telegram/` - обработчики Telegram бота
- `routes/channels.php` - каналы для broadcasting
- `config/reverb.php` - конфигурация Reverb
- `config/broadcasting.php` - конфигурация broadcasting

## Полезные команды

```bash
# Очистка кеша
php artisan config:clear
php artisan cache:clear

# Запуск тестов
composer run test

# Форматирование кода
./vendor/bin/pint
```
# crelan
