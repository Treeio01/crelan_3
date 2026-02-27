# 📦 Полная инструкция по установке проекта Crelan Laravel

## 📋 Содержание
1. [Системные требования](#системные-требования)
2. [Установка зависимостей](#установка-зависимостей)
3. [Настройка окружения](#настройка-окружения)
4. [Настройка базы данных](#настройка-базы-данных)
5. [Сборка фронтенда](#сборка-фронтенда)
6. [Настройка Supervisor](#настройка-supervisor)
7. [Настройка веб-сервера](#настройка-веб-сервера)
8. [Проверка работоспособности](#проверка-работоспособности)
9. [Управление процессами](#управление-процессами)

---

## 🔧 Системные требования

### Минимальные требования:
- **ОС**: Ubuntu 20.04+ / Debian 11+ / CentOS 8+ / macOS
- **Git**: >= 2.0 (для клонирования репозитория)
- **PHP**: >= 8.3 с расширениями:
  - `php-cli`
  - `php-fpm`
  - `php-mbstring`
  - `php-xml`
  - `php-curl`
  - `php-zip`
  - `php-gd`
  - `php-sqlite3` (или `php-mysql`, `php-pgsql` в зависимости от БД)
  - `php-bcmath`
  - `php-intl`
- **Composer**: >= 2.0
- **Node.js**: >= 18.x
- **npm**: >= 9.x
- **Supervisor**: >= 4.0
- **Nginx** или **Apache** (для продакшена)

### Рекомендуемые требования:
- **RAM**: минимум 2GB (рекомендуется 4GB+)
- **CPU**: минимум 2 ядра
- **Диск**: минимум 10GB свободного места

---

## 📥 Установка зависимостей

### 1. Установка системных пакетов (Ubuntu/Debian)

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка Git (если еще не установлен)
sudo apt install -y git

# Установка необходимых утилит
sudo apt install -y software-properties-common lsb-release ca-certificates apt-transport-https

# Добавление репозитория для PHP 8.3 (Ubuntu/Debian)
# Для Ubuntu:
sudo add-apt-repository ppa:ondrej/php -y

# Для Debian (альтернативный способ):
# curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /usr/share/keyrings/deb.sury.org-php.gpg
# echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list

# Обновление списка пакетов после добавления репозитория
sudo apt update

# Установка PHP 8.3 и расширений
sudo apt install -y php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 php8.3-bcmath \
    php8.3-intl php8.3-mysql

# Установка дополнительных расширений для Laravel
sudo apt install -y php8.3-pdo php8.3-pdo-mysql php8.3-pdo-sqlite \
    php8.3-tokenizer php8.3-fileinfo

# Проверка версии PHP
php -v

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Установка Node.js (через NodeSource)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Установка Supervisor
sudo apt install -y supervisor

# Установка Nginx (опционально)
sudo apt install -y nginx
```

### 2. Клонирование проекта

```bash
# Перейдите в директорию для проектов
cd /var/www  # или другая директория по вашему выбору

# Клонируйте репозиторий
git clone https://github.com/Treeio01/crelan crelan
cd crelan

# Установите права доступа
sudo chown -R www-data:www-data /var/www/crelan
sudo chmod -R 755 /var/www/crelan
sudo chmod -R 775 /var/www/crelan/storage
sudo chmod -R 775 /var/www/crelan/bootstrap/cache
```

### 3. Установка PHP зависимостей

```bash
cd /var/www/crelan
composer install --optimize-autoloader --no-dev
```

### 4. Установка Node.js зависимостей

```bash
npm install
```

---

## ⚙️ Настройка окружения

### 1. Создание файла `.env`

```bash
cp .env.example .env
php artisan key:generate
```

### 2. Настройка переменных окружения

Откройте файл `.env` и настройте следующие параметры:

```env
# Приложение
APP_NAME="Crelan"
APP_ENV=production
APP_KEY=base64:...  # Генерируется автоматически
APP_DEBUG=false
APP_TIMEZONE=Europe/Brussels
APP_URL=https://your-domain.com

# База данных
DB_CONNECTION=mysql  # или sqlite, pgsql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crelan_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Очередь задач
QUEUE_CONNECTION=database

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
SUPER_ADMIN_TELEGRAM_ID=your_telegram_user_id
TELEGRAM_GROUP_CHAT_ID=your_group_chat_id

# Cloudflare (опционально)
CLOUDFLARE_API_TOKEN=your_cloudflare_api_token
# ИЛИ
CLOUDFLARE_API_EMAIL=your_email
CLOUDFLARE_API_KEY=your_api_key
CLOUDFLARE_DEFAULT_IP=192.168.1.1

# Reverb (WebSockets)
REVERB_APP_ID=your_reverb_app_id
REVERB_APP_KEY=your_reverb_app_key
REVERB_APP_SECRET=your_reverb_app_secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https
```

### 3. Генерация ключей Reverb

```bash
php artisan reverb:keys
```

Скопируйте сгенерированные ключи в `.env` файл.

---

## 🗄️ Настройка базы данных

### Вариант 1: MySQL/MariaDB

#### Установка MySQL/MariaDB (если еще не установлен)

```bash
# Установка MySQL сервера и клиента
sudo apt update
sudo apt install -y mysql-server mysql-client

# Или установка MariaDB (альтернатива MySQL)
# sudo apt install -y mariadb-server mariadb-client

# Запуск и включение автозапуска MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Безопасная настройка MySQL (рекомендуется)
sudo mysql_secure_installation
```

#### Создание базы данных

```bash
# Подключение к MySQL
mysql -u root -p
# Или если используется sudo:
sudo mysql -u root
```

```sql
-- Создание базы данных
CREATE DATABASE crelan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создание пользователя (замените 'strong_password' на надежный пароль)
CREATE USER 'crelan_user'@'localhost' IDENTIFIED BY 'qpareiosngjtrh234';

-- Предоставление прав
GRANT ALL PRIVILEGES ON crelan_db.* TO 'crelan_user'@'localhost';

-- Применение изменений
FLUSH PRIVILEGES;

-- Выход из MySQL
EXIT;
```

**Примечание:** Если вы получаете ошибку доступа при использовании `mysql -u root -p`, попробуйте:
```bash
# Использовать sudo для доступа без пароля
sudo mysql -u root

# Или сбросить пароль root (если забыли)
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_new_password';
FLUSH PRIVILEGES;
EXIT;
```

### Вариант 2: SQLite

```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

### Выполнение миграций

```bash
php artisan migrate --force
```

### Создание первого супер-админа (опционально)

```bash
php artisan db:seed --class=AdminSeeder
```

Или создайте админа вручную через Telegram бота после первого запуска.

---

## 🎨 Сборка фронтенда

```bash
npm run build
```

Для продакшена используйте:
```bash
npm run build -- --mode production
```

---

## 🔄 Настройка Supervisor

Supervisor будет управлять следующими процессами:
1. **Telegram Bot** (long polling)
2. **Laravel Queue Worker** (обработка очередей)
3. **Laravel Reverb** (WebSocket сервер)

### 1. Создание конфигурационных файлов Supervisor

#### Telegram Bot (`/etc/supervisor/conf.d/crelan-telegram-bot.conf`)

```ini
[program:crelan-telegram-bot]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/crelan/artisan telegram:bot
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/crelan/storage/logs/telegram-bot.log
stopwaitsecs=3600
```

#### Laravel Queue Worker (`/etc/supervisor/conf.d/crelan-queue-worker.conf`)

```ini
[program:crelan-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/crelan/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/crelan/storage/logs/queue-worker.log
stopwaitsecs=3600
```

#### Laravel Reverb (`/etc/supervisor/conf.d/crelan-reverb.conf`)

```ini
[program:crelan-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/crelan/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/crelan/storage/logs/reverb.log
stopwaitsecs=10
```

### 2. Применение конфигурации Supervisor

```bash
# Перезагрузка конфигурации Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Запуск всех процессов
sudo supervisorctl start crelan-telegram-bot:*
sudo supervisorctl start crelan-queue-worker:*
sudo supervisorctl start crelan-reverb:*

# Проверка статуса
sudo supervisorctl status
```

### 3. Настройка автозапуска Supervisor

```bash
# Включить автозапуск Supervisor при загрузке системы
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 🌐 Настройка веб-сервера

### Вариант 1: Nginx

Создайте файл `/etc/nginx/sites-available/crelan`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    
    # Редирект на HTTPS (раскомментируйте после настройки SSL)
    # return 301 https://$server_name$request_uri;
    
    root /var/www/crelan/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # WebSocket прокси для Reverb
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}

# HTTPS конфигурация (после получения SSL сертификата)
# server {
#     listen 443 ssl http2;
#     listen [::]:443 ssl http2;
#     server_name your-domain.com;
#     
#     ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
#     
#     # Остальная конфигурация аналогична HTTP
#     root /var/www/crelan/public;
#     index index.php;
#     
#     # ... остальные настройки ...
# }
```

Активируйте конфигурацию:

```bash
sudo ln -s /etc/nginx/sites-available/crelan /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Вариант 2: Apache2

#### 1. Установка и включение необходимых модулей

```bash
# Установка Apache2 (если еще не установлен)
sudo apt install -y apache2

# Включение необходимых модулей
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod proxy_fcgi  # Для работы с PHP-FPM
sudo a2enmod ssl

# Перезапуск Apache для применения модулей
sudo systemctl restart apache2
```

#### 2. Создание конфигурации виртуального хоста

Создайте файл `/etc/apache2/sites-available/crelan.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com
    DocumentRoot /var/www/crelan/public

    <Directory /var/www/crelan/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # WebSocket прокси для Reverb
    ProxyPreserveHost On
    ProxyRequests Off
    
    # WebSocket для Reverb
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]
    
    # HTTP прокси для Reverb (fallback)
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/

    # PHP-FPM через mod_proxy_fcgi
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Логи
    ErrorLog ${APACHE_LOG_DIR}/crelan-error.log
    CustomLog ${APACHE_LOG_DIR}/crelan-access.log combined
    
    # Безопасность
    <DirectoryMatch "^/.*/\.git/">
        Require all denied
    </DirectoryMatch>
</VirtualHost>

# HTTPS конфигурация (после получения SSL сертификата)
# Раскомментируйте после настройки SSL
# <VirtualHost *:443>
#     ServerName your-domain.com
#     ServerAdmin admin@your-domain.com
#     DocumentRoot /var/www/crelan/public
# 
#     SSLEngine on
#     SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
#     SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem
# 
#     <Directory /var/www/crelan/public>
#         Options -Indexes +FollowSymLinks
#         AllowOverride All
#         Require all granted
#     </Directory>
# 
#     # WebSocket прокси для Reverb
#     ProxyPreserveHost On
#     ProxyRequests Off
#     
#     RewriteEngine On
#     RewriteCond %{HTTP:Upgrade} websocket [NC]
#     RewriteCond %{HTTP:Connection} upgrade [NC]
#     RewriteRule ^/app/(.*)$ wss://127.0.0.1:8080/app/$1 [P,L]
#     
#     ProxyPass /app/ http://127.0.0.1:8080/app/
#     ProxyPassReverse /app/ http://127.0.0.1:8080/app/
# 
#     <FilesMatch \.php$>
#         SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
#     </FilesMatch>
# 
#     ErrorLog ${APACHE_LOG_DIR}/crelan-ssl-error.log
#     CustomLog ${APACHE_LOG_DIR}/crelan-ssl-access.log combined
# </VirtualHost>
```

#### 3. Альтернативная конфигурация с mod_php (если не используете PHP-FPM)

Если вы используете mod_php вместо PHP-FPM, используйте следующую конфигурацию:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/crelan/public

    <Directory /var/www/crelan/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # WebSocket прокси для Reverb
    ProxyPreserveHost On
    ProxyRequests Off
    
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/app/(.*)$ ws://127.0.0.1:8080/app/$1 [P,L]
    
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/

    ErrorLog ${APACHE_LOG_DIR}/crelan-error.log
    CustomLog ${APACHE_LOG_DIR}/crelan-access.log combined
</VirtualHost>
```

#### 4. Активация конфигурации

```bash
# Активируйте сайт
sudo a2ensite crelan.conf

# Отключите дефолтный сайт (опционально)
sudo a2dissite 000-default.conf

# Проверьте конфигурацию на ошибки
sudo apache2ctl configtest

# Перезагрузите Apache
sudo systemctl reload apache2
# или
sudo systemctl restart apache2
```

#### 5. Настройка прав доступа

```bash
# Установите правильного владельца
sudo chown -R www-data:www-data /var/www/crelan

# Установите права на директории
sudo find /var/www/crelan -type d -exec chmod 755 {} \;

# Установите права на файлы
sudo find /var/www/crelan -type f -exec chmod 644 {} \;

# Специальные права для storage и cache
sudo chmod -R 775 /var/www/crelan/storage
sudo chmod -R 775 /var/www/crelan/bootstrap/cache
```

#### 6. Настройка .htaccess (если нужно)

Убедитесь, что файл `/var/www/crelan/public/.htaccess` существует и содержит:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### 7. Настройка нескольких доменов

**🚀 Автоматическое добавление доменов через Telegram бота (РЕКОМЕНДУЕТСЯ)**

Проект имеет встроенную интеграцию с Cloudflare API для автоматического управления доменами. Это намного проще, чем ручная настройка Apache!

**Как использовать:**

1. **Настройте Cloudflare API** в `.env`:
```env
CLOUDFLARE_API_TOKEN=your_cloudflare_api_token
# ИЛИ
CLOUDFLARE_API_EMAIL=your_email
CLOUDFLARE_API_KEY=your_api_key
```

2. **Откройте Telegram бота** и нажмите кнопку **"🌐 Домены"**

3. **Добавьте домен:**
   - Нажмите **"➕ Добавить домен"**
   - Отправьте в формате: `домен IP`
   - Например: `example.com 192.168.1.1`

4. **Бот автоматически:**
   - ✅ Создаст зону в Cloudflare
   - ✅ Добавит A запись с указанным IP
   - ✅ Установит SSL режим Flexible
   - ✅ Получит NS записи
   - ✅ Сохранит информацию в БД

5. **Настройте NS записи у регистратора:**
   - Бот покажет NS записи после добавления домена
   - Скопируйте их и настройте у вашего регистратора домена

6. **Обновите Apache конфигурацию** (один раз):
   
   Создайте виртуальный хост с поддержкой всех доменов через ServerAlias:

```apache
<VirtualHost *:80>
    ServerName homedome214.com
    ServerAlias *.homedome214.com *.example.com *.domain2.com
    
    DocumentRoot /var/www/crelan/public

    <Directory /var/www/crelan/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # WebSocket proxy для Reverb
    ProxyPreserveHost On
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} =websocket [NC]
    RewriteRule /app/(.*) ws://127.0.0.1:8080/app/$1 [P,L]
    ProxyPass /app ws://127.0.0.1:8080/app
    ProxyPassReverse /app ws://127.0.0.1:8080/app

    # PHP-FPM через mod_proxy_fcgi
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/crelan-error.log
    CustomLog ${APACHE_LOG_DIR}/crelan-access.log combined
</VirtualHost>
```

**Или используйте wildcard для всех доменов:**

```apache
<VirtualHost *:80>
    ServerName homedome214.com
    ServerAlias *
    
    DocumentRoot /var/www/crelan/public

    <Directory /var/www/crelan/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # WebSocket proxy для Reverb
    ProxyPreserveHost On
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} =websocket [NC]
    RewriteRule /app/(.*) ws://127.0.0.1:8080/app/$1 [P,L]
    ProxyPass /app ws://127.0.0.1:8080/app
    ProxyPassReverse /app ws://127.0.0.1:8080/app

    # PHP-FPM через mod_proxy_fcgi
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/crelan-error.log
    CustomLog ${APACHE_LOG_DIR}/crelan-access.log combined
</VirtualHost>
```

**После добавления домена через бота:**
- Домен автоматически настроен в Cloudflare
- DNS записи созданы
- SSL настроен (Flexible)
- Осталось только настроить NS у регистратора

**Управление доменами через бота:**
- 📋 **Список доменов** — просмотр всех добавленных доменов
- ℹ️ **Информация о домене** — NS записи, IP, статус
- ✏️ **Изменить IP** — обновление IP адреса домена

**Преимущества автоматического способа:**
- ✅ Не нужно вручную настраивать DNS
- ✅ Автоматическая настройка SSL
- ✅ Управление через удобный интерфейс бота
- ✅ История всех доменов в БД
- ✅ Проверка статуса работы доменов

#### 8. Проверка работы Apache

```bash
# Проверьте статус Apache
sudo systemctl status apache2

# Проверьте логи на ошибки
sudo tail -f /var/log/apache2/error.log

# Проверьте доступность сайта
curl -I http://your-domain.com

# Проверьте конфигурацию Apache
sudo apache2ctl -S
```

Команда `apache2ctl -S` покажет все активные виртуальные хосты и их настройки.

---

## ✅ Проверка работоспособности

### 1. Проверка процессов Supervisor

```bash
sudo supervisorctl status
```

Должны быть запущены:
- `crelan-telegram-bot_00`
- `crelan-queue-worker_00`
- `crelan-queue-worker_01`
- `crelan-reverb_00`

### 2. Проверка логов

```bash
# Логи Telegram бота
tail -f /var/www/crelan/storage/logs/telegram-bot.log

# Логи Queue Worker
tail -f /var/www/crelan/storage/logs/queue-worker.log

# Логи Reverb
tail -f /var/www/crelan/storage/logs/reverb.log

# Логи Laravel
tail -f /var/www/crelan/storage/logs/laravel.log
```

### 3. Проверка веб-интерфейса

Откройте в браузере: `http://your-domain.com`

### 4. Проверка Telegram бота

Отправьте команду `/start` боту в Telegram.

### 5. Проверка WebSocket соединения

Откройте консоль браузера на главной странице и проверьте подключение к WebSocket.

---

## 🎮 Управление процессами

### Команды Supervisor

```bash
# Просмотр статуса всех процессов
sudo supervisorctl status

# Запуск процесса
sudo supervisorctl start crelan-telegram-bot:*

# Остановка процесса
sudo supervisorctl stop crelan-telegram-bot:*

# Перезапуск процесса
sudo supervisorctl restart crelan-telegram-bot:*

# Перезапуск всех процессов проекта
sudo supervisorctl restart crelan-telegram-bot:*
sudo supervisorctl restart crelan-queue-worker:*
sudo supervisorctl restart crelan-reverb:*

# Просмотр логов в реальном времени
sudo supervisorctl tail -f crelan-telegram-bot:*
```

### Команды Laravel

```bash
# Очистка кеша
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Оптимизация для продакшена
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Перезапуск очереди (после изменений в коде)
sudo supervisorctl restart crelan-queue-worker:*

# Перезапуск бота (после изменений в коде)
sudo supervisorctl restart crelan-telegram-bot:*
```

---

## 🔒 Безопасность

### 1. Настройка прав доступа

```bash
# Владелец файлов
sudo chown -R www-data:www-data /var/www/crelan

# Права на директории
find /var/www/crelan -type d -exec chmod 755 {} \;

# Права на файлы
find /var/www/crelan -type f -exec chmod 644 {} \;

# Права на storage и cache
chmod -R 775 /var/www/crelan/storage
chmod -R 775 /var/www/crelan/bootstrap/cache
```

### 2. Настройка файрвола

```bash
# Разрешить HTTP, HTTPS и WebSocket
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp  # Для Reverb (если нужен прямой доступ)

# Включить файрвол
sudo ufw enable
```

### 3. SSL сертификат (Let's Encrypt)

#### Для Nginx:

```bash
# Установка Certbot для Nginx
sudo apt install certbot python3-certbot-nginx

# Получение сертификата
sudo certbot --nginx -d your-domain.com

# Автоматическое обновление
sudo certbot renew --dry-run
```

#### Для Apache2:

```bash
# Установка Certbot для Apache
sudo apt install certbot python3-certbot-apache

# Получение сертификата
sudo certbot --apache -d your-domain.com

# Certbot автоматически настроит SSL и обновит конфигурацию Apache

# Автоматическое обновление
sudo certbot renew --dry-run
```

После получения сертификата Certbot автоматически:
- Настроит HTTPS виртуальный хост
- Добавит редирект с HTTP на HTTPS
- Настроит автоматическое обновление сертификата

---

## 📝 Дополнительные настройки

### Настройка часового пояса

```bash
sudo timedatectl set-timezone Europe/Brussels
```

### Настройка cron для Laravel Scheduler (если используется)

Добавьте в crontab:

```bash
sudo crontab -e -u www-data
```

```
* * * * * cd /var/www/crelan && php artisan schedule:run >> /dev/null 2>&1
```

### Ротация логов

Создайте файл `/etc/logrotate.d/crelan`:

```
/var/www/crelan/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## 🐛 Решение проблем

### Проблема: PHP 8.3 пакеты не найдены

Если при установке PHP 8.3 вы получаете ошибку "Unable to locate package", выполните следующие шаги:

```bash
# 1. Установите необходимые утилиты
sudo apt install -y software-properties-common lsb-release ca-certificates apt-transport-https gnupg2

# 2. Для Ubuntu - добавьте PPA репозиторий
sudo add-apt-repository ppa:ondrej/php -y

# 3. Для Debian - добавьте репозиторий Sury
# Получите ключ GPG
curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /usr/share/keyrings/deb.sury.org-php.gpg

# Добавьте репозиторий
echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list

# 4. Обновите список пакетов
sudo apt update

# 5. Проверьте доступность пакетов
apt-cache search php8.3 | head -20

# 6. Если пакеты найдены, установите PHP 8.3
sudo apt install -y php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 php8.3-bcmath \
    php8.3-intl php8.3-mysql php8.3-pdo php8.3-pdo-mysql php8.3-pdo-sqlite \
    php8.3-tokenizer php8.3-fileinfo
```

**Альтернативный способ для Debian:**

```bash
# Установка через официальный репозиторий Debian (если доступен PHP 8.3)
sudo apt update
sudo apt install -y php-cli php-fpm php-mbstring php-xml \
    php-curl php-zip php-gd php-sqlite3 php-bcmath \
    php-intl php-mysql php-pdo php-pdo-mysql php-pdo-sqlite \
    php-tokenizer php-fileinfo

# Проверьте версию (должна быть 8.3 или выше)
php -v
```

**Если PHP 8.3 недоступен в репозиториях:**

Можно использовать PHP 8.2 (минимальная версия для проекта) или установить PHP из исходников:

```bash
# Проверьте доступные версии PHP в репозиториях
apt-cache search php | grep "^php[0-9]"

# Если доступна только PHP 8.2, используйте её
sudo apt install -y php8.2-cli php8.2-fpm php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-gd php8.2-sqlite3 php8.2-bcmath \
    php8.2-intl php8.2-mysql php8.2-pdo php8.2-pdo-mysql php8.2-pdo-sqlite \
    php8.2-tokenizer php8.2-fileinfo
```

### Проблема: Supervisor не запускает процессы

```bash
# Проверьте логи Supervisor
sudo tail -f /var/log/supervisor/supervisord.log

# Проверьте права доступа
ls -la /var/www/crelan/artisan

# Проверьте синтаксис конфигурации
sudo supervisorctl reread
```

### Проблема: Telegram бот не отвечает

```bash
# Проверьте токен в .env
grep TELEGRAM_BOT_TOKEN .env

# Проверьте логи бота
tail -f storage/logs/telegram-bot.log

# Перезапустите бота
sudo supervisorctl restart crelan-telegram-bot:*
```

### Проблема: WebSocket не подключается

```bash
# Проверьте, запущен ли Reverb
sudo supervisorctl status crelan-reverb:*

# Проверьте порт 8080
sudo netstat -tlnp | grep 8080

# Проверьте конфигурацию Nginx для WebSocket
```

### Проблема: Очередь не обрабатывается

```bash
# Проверьте таблицу jobs в БД
php artisan tinker
>>> DB::table('jobs')->count();

# Перезапустите worker
sudo supervisorctl restart crelan-queue-worker:*
```

---

## 📞 Поддержка

При возникновении проблем проверьте:
1. Логи в `/var/www/crelan/storage/logs/`
2. Логи Supervisor в `/var/log/supervisor/`
3. Логи веб-сервера (Nginx/Apache)
4. Статус процессов: `sudo supervisorctl status`

---

## 🎉 Готово!

После выполнения всех шагов ваш проект должен быть полностью настроен и работать в продакшене.

**Проверочный список:**
- ✅ Все зависимости установлены
- ✅ `.env` файл настроен
- ✅ База данных создана и миграции выполнены
- ✅ Фронтенд собран
- ✅ Supervisor настроен и процессы запущены
- ✅ Веб-сервер настроен
- ✅ SSL сертификат установлен (для продакшена)
- ✅ Telegram бот отвечает на команды
- ✅ WebSocket соединение работает
