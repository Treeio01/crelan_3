# 10. АРХИТЕКТУРА ПРОЕКТА

## 🏗️ Архитектурные паттерны:

Проект должен быть построен с использованием следующих паттернов и компонентов:

### ✅ Request (Form Requests)
- Валидация входящих данных
- Отдельные классы для каждого типа запроса
- Примеры:
  - `CreateSessionRequest` - создание сессии
  - `SubmitFormRequest` - отправка данных формы
  - `AssignSessionRequest` - назначение админа на сессию

### ✅ Response (API Resources)
- Форматирование ответов API
- Трансформация данных для клиента
- Примеры:
  - `SessionResource` - данные сессии
  - `SessionCollection` - коллекция сессий
  - `AdminResource` - данные админа

### ✅ DTO (Data Transfer Objects)
- Объекты для передачи данных между слоями
- Типизированные данные
- Примеры:
  - `SessionDTO` - данные сессии
  - `FormDataDTO` - данные формы
  - `TelegramMessageDTO` - данные для Telegram сообщения

### ✅ Actions (Action Classes)
- Бизнес-логика в отдельных классах
- Один Action = одно действие
- Примеры:
  - `CreateSessionAction` - создание сессии
  - `AssignSessionAction` - назначение админа
  - `SubmitFormAction` - обработка данных формы
  - `SendTelegramMessageAction` - отправка сообщения в Telegram
  - `AddAdminAction` - добавление нового админа
  - `GetAdminProfileAction` - получение профиля админа

### ✅ Observers
- Наблюдатели за моделями
- Автоматические действия при изменениях
- Примеры:
  - `SessionObserver` - отслеживание изменений сессий
    - При создании → отправка в Telegram
    - При обновлении → обновление сообщения в Telegram
    - При изменении статуса → WebSocket событие

### ✅ Events (Laravel Events)
- События приложения
- Декомпозиция логики
- Примеры:
  - `SessionCreated` - сессия создана
  - `SessionAssigned` - админ назначен
  - `FormSubmitted` - форма отправлена
  - `SessionStatusChanged` - статус сессии изменен

### ✅ Listeners
- Обработчики событий
- Асинхронная обработка где возможно
- Примеры:
  - `SendTelegramNotificationListener` - отправка уведомления в Telegram
  - `BroadcastSessionEventListener` - отправка WebSocket события
  - `UpdateSessionHistoryListener` - запись в историю

### ✅ Service (Service Classes)
- Сервисы для сложной бизнес-логики
- Переиспользуемая логика
- Примеры:
  - `SessionService` - работа с сессиями
  - `TelegramService` - работа с Telegram API
  - `WebSocketService` - работа с WebSocket broadcasting
  - `FormService` - обработка форм
  - `AdminService` - работа с админами, проверка доступа

## 📁 Структура проекта:

```
app/
├── Actions/
│   ├── Session/
│   │   ├── CreateSessionAction.php
│   │   ├── AssignSessionAction.php
│   │   ├── UnassignSessionAction.php
│   │   ├── SubmitFormAction.php
│   │   └── CompleteSessionAction.php
│   ├── Admin/
│   │   └── AddAdminAction.php
│   └── Telegram/
│       └── SendMessageAction.php
│
├── DTOs/
│   ├── SessionDTO.php
│   ├── FormDataDTO.php
│   └── TelegramMessageDTO.php
│
├── Events/
│   ├── SessionCreated.php
│   ├── SessionAssigned.php
│   ├── SessionUnassigned.php
│   ├── FormSubmitted.php
│   └── SessionStatusChanged.php
│
├── Listeners/
│   ├── SendTelegramNotificationListener.php
│   ├── BroadcastSessionEventListener.php
│   └── UpdateSessionHistoryListener.php
│
├── Http/
│   ├── Controllers/
│   │   ├── SessionController.php
│   │   ├── FormController.php
│   │   └── AdminController.php
│   ├── Requests/
│   │   ├── CreateSessionRequest.php
│   │   ├── SubmitFormRequest.php
│   │   └── AssignSessionRequest.php
│   └── Resources/
│       ├── SessionResource.php
│       └── SessionCollection.php
│
├── Models/
│   ├── Session.php
│   ├── Admin.php
│   └── SessionHistory.php
│
├── Observers/
│   └── SessionObserver.php
│
├── Services/
│   ├── SessionService.php
│   ├── TelegramService.php
│   ├── WebSocketService.php
│   └── FormService.php
│
└── Telegram/
    ├── TelegramBot.php
    ├── Middleware/
    │   └── AdminAuthMiddleware.php - проверка доступа админа
    ├── Handlers/
    │   ├── AdminPanelHandler.php
    │   ├── SessionHandler.php
    │   ├── ActionHandler.php
    │   └── ProfileHandler.php - обработчик профиля
    └── Keyboards/
        ├── AdminKeyboard.php
        └── ProfileKeyboard.php - клавиатура профиля
```

## 🔄 Поток данных:

### Пример: Создание сессии

1. **Request** → `CreateSessionRequest` (валидация)
2. **Controller** → `SessionController@create` (принимает Request)
3. **Action** → `CreateSessionAction` (бизнес-логика)
   - Использует `SessionService` для создания
   - Возвращает `SessionDTO`
4. **Model** → `Session::create()` (сохранение в БД)
5. **Observer** → `SessionObserver@created` (триггер)
6. **Event** → `SessionCreated` (диспатч события)
7. **Listeners**:
   - `SendTelegramNotificationListener` → отправка в Telegram
   - `BroadcastSessionEventListener` → WebSocket broadcast
   - `UpdateSessionHistoryListener` → запись в историю
8. **Response** → `SessionResource` (форматирование ответа)

### Пример: Админ выбирает действие

1. **Telegram Handler** → `ActionHandler@handleCode`
2. **Action** → `SelectActionAction` (сохранение действия)
3. **Service** → `SessionService@updateAction`
4. **Model** → `Session::update()` (обновление БД)
5. **Observer** → `SessionObserver@updated` (триггер)
6. **Event** → `SessionStatusChanged` (диспатч)
7. **Listeners**:
   - `BroadcastSessionEventListener` → WebSocket событие `action.code`
   - `SendTelegramNotificationListener` → обновление сообщения в Telegram
8. **Frontend** → получает WebSocket событие → редирект на форму

## 🎯 Принципы:

### SOLID:
- **S**ingle Responsibility - каждый класс одна ответственность
- **O**pen/Closed - открыт для расширения, закрыт для модификации
- **L**iskov Substitution - подклассы заменяют базовые классы
- **I**nterface Segregation - интерфейсы разделены по назначению
- **D**ependency Inversion - зависимость от абстракций

### DDD (Domain-Driven Design):
- Доменные модели (Session, Admin)
- Сервисы домена (SessionService)
- События домена (SessionCreated)
- Value Objects (SessionDTO)

### DI (Dependency Injection):
- Все зависимости через конструктор
- Использование Laravel Service Container
- Интерфейсы для сервисов

### TDD (Test-Driven Development):
- Покрытие тестами всех Actions
- Тесты для Services
- Тесты для Events и Listeners

## 📝 Примеры реализации:

### Action класс:
```php
class CreateSessionAction
{
    public function __construct(
        private SessionService $sessionService,
        private WebSocketService $webSocketService
    ) {}

    public function execute(CreateSessionDTO $dto): SessionDTO
    {
        // Бизнес-логика создания сессии
        // Возвращает DTO
    }
}
```

### Service класс:
```php
class SessionService
{
    public function create(array $data): Session
    {
        // Создание сессии
        // Возвращает модель
    }
}
```

### Observer:
```php
class SessionObserver
{
    public function created(Session $session): void
    {
        event(new SessionCreated($session));
    }
    
    public function updated(Session $session): void
    {
        event(new SessionStatusChanged($session));
    }
}
```

### Event + Listener:
```php
class SessionCreated
{
    public function __construct(public Session $session) {}
}

class SendTelegramNotificationListener
{
    public function handle(SessionCreated $event): void
    {
        // Отправка в Telegram
    }
}
```
