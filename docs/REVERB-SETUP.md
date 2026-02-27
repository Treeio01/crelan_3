# Настройка Laravel Reverb

## Переменные окружения (.env)

Добавьте следующие переменные в файл `.env`:

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb Server Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (Frontend) - должны совпадать с серверными
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Для продакшена (HTTPS)

```env
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Запуск Reverb сервера

```bash
php artisan reverb:start
```

Для продакшена с debug:
```bash
php artisan reverb:start --debug
```

## Сборка фронтенда

После настройки переменных пересоберите фронтенд:

```bash
npm run build
```

Для разработки:
```bash
npm run dev
```

## Проверка работы

1. Запустите Reverb сервер: `php artisan reverb:start`
2. Откройте консоль браузера
3. Должно быть сообщение: `[Echo] Connected to Reverb WebSocket server`

## Troubleshooting

### Ошибка подключения
- Проверьте что Reverb сервер запущен
- Проверьте что порт не занят
- Проверьте CORS настройки

### События не приходят
- Проверьте что BROADCAST_CONNECTION=reverb
- Проверьте логи Laravel: `storage/logs/laravel.log`
