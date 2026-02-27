#!/bin/bash

# Скрипт для автоматической настройки Supervisor для проекта Crelan Laravel
# Использование: sudo ./scripts/setup-supervisor.sh

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Получаем путь к проекту
PROJECT_PATH=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
PROJECT_NAME="crelan"

echo -e "${GREEN}🚀 Настройка Supervisor для проекта Crelan Laravel${NC}"
echo ""

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Ошибка: Этот скрипт должен быть запущен с правами root (sudo)${NC}"
    exit 1
fi

# Проверка существования директории проекта
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ Ошибка: Директория проекта не найдена: $PROJECT_PATH${NC}"
    exit 1
fi

# Проверка существования artisan
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo -e "${RED}❌ Ошибка: Файл artisan не найден в директории проекта${NC}"
    exit 1
fi

# Определяем пользователя веб-сервера
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
else
    echo -e "${YELLOW}⚠️  Предупреждение: Пользователь веб-сервера не найден. Используется текущий пользователь.${NC}"
    WEB_USER=$(whoami)
fi

echo -e "${GREEN}📁 Путь к проекту: $PROJECT_PATH${NC}"
echo -e "${GREEN}👤 Пользователь: $WEB_USER${NC}"
echo ""

# Создание директории для логов, если её нет
mkdir -p "$PROJECT_PATH/storage/logs"
chown -R $WEB_USER:$WEB_USER "$PROJECT_PATH/storage/logs"
chmod -R 775 "$PROJECT_PATH/storage/logs"

# Конфигурация Telegram Bot
echo -e "${GREEN}📝 Создание конфигурации Telegram Bot...${NC}"
cat > /etc/supervisor/conf.d/${PROJECT_NAME}-telegram-bot.conf <<EOF
[program:${PROJECT_NAME}-telegram-bot]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan telegram:bot
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/telegram-bot.log
stopwaitsecs=3600
EOF

# Конфигурация Queue Worker
echo -e "${GREEN}📝 Создание конфигурации Queue Worker...${NC}"
cat > /etc/supervisor/conf.d/${PROJECT_NAME}-queue-worker.conf <<EOF
[program:${PROJECT_NAME}-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/queue-worker.log
stopwaitsecs=3600
EOF

# Конфигурация Reverb
echo -e "${GREEN}📝 Создание конфигурации Reverb...${NC}"
cat > /etc/supervisor/conf.d/${PROJECT_NAME}-reverb.conf <<EOF
[program:${PROJECT_NAME}-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/reverb.log
stopwaitsecs=10
EOF

# Применение конфигурации
echo -e "${GREEN}🔄 Применение конфигурации Supervisor...${NC}"
supervisorctl reread
supervisorctl update

# Запуск процессов
echo -e "${GREEN}▶️  Запуск процессов...${NC}"
supervisorctl start ${PROJECT_NAME}-telegram-bot:*
supervisorctl start ${PROJECT_NAME}-queue-worker:*
supervisorctl start ${PROJECT_NAME}-reverb:*

# Проверка статуса
echo ""
echo -e "${GREEN}✅ Статус процессов:${NC}"
supervisorctl status | grep ${PROJECT_NAME} || true

echo ""
echo -e "${GREEN}✅ Настройка Supervisor завершена успешно!${NC}"
echo ""
echo -e "${YELLOW}📋 Полезные команды:${NC}"
echo "  Просмотр статуса: sudo supervisorctl status"
echo "  Просмотр логов: sudo supervisorctl tail -f ${PROJECT_NAME}-telegram-bot:*"
echo "  Перезапуск бота: sudo supervisorctl restart ${PROJECT_NAME}-telegram-bot:*"
echo "  Перезапуск очереди: sudo supervisorctl restart ${PROJECT_NAME}-queue-worker:*"
echo "  Перезапуск Reverb: sudo supervisorctl restart ${PROJECT_NAME}-reverb:*"
echo ""
