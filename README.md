# Ремонтная служба — система управления заявками

Веб-приложение для приёма и обработки заявок в ремонтную службу с разделением ролей (диспетчер, мастер).  
Позволяет создавать заявки, назначать мастеров, отслеживать статусы и безопасно брать заявки в работу (с защитой от гонок).

## Требования к окружению

- **Docker** и **Docker Compose** (версия 3.8+)
- Порт 80, 5173, 5432, 3306, 6379 не должны быть заняты (или измените в docker-compose.yml)

## Быстрый старт

1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/UnDeadWindAngel/request-for-repair-service.git
   cd request-for-repair-service
   ```

2. Скопируйте файлы окружения:
   ```bash
   cp backend/.env.example backend/.env
   cp .env.example .env
   cp .env.dev.example .env.dev
   cp .env.test.example .env.test
   cp .env.prod.example .env.prod
   ```
   
#### Важно:
- Сгенерируйте ключ приложения:
   ```bash
   php artisan key:generate
   ```
- Настройте параметры подключения к БД в соответствии с вашей средой (пароли, имена баз).
- Для работы очередей убедитесь, что Redis доступен.

3. После настройки всех файлов окружения запустите контейнеры одной из команд:
- Для окружения разработки
   ```bash
   docker-compose --profile dev --env-file .env.dev up -d --build
   ```
- Для окружения тестов
   ```bash
   docker-compose --profile test --env-file .env.test up -d --build
   ```
#### Важно:
- если не хотите после запуска тестов снова заполнять бд разработки то тесты стоит запускать в тестовом окружении.

4. Выполните миграции и наполните базу тестовыми данными:
   ```bash
   docker compose exec backend php artisan migrate --seed
   ```

5. Откройте браузер и перейдите по адресу:
- Фронтенд: http://localhost:5173 (Vite dev server)
- Или если статика раздаётся через nginx: http://localhost

Готово! Приложение доступно для использования.

## Тестовые пользователи

После запуска и выполнения `php artisan db:seed` в системе будут доступны следующие учётные записи:

| Роль       | Имя            | Email                  | Пароль   |
|------------|----------------|------------------------|----------|
| Диспетчер  | Dispatcher One | dispatcher1@example.com| password |
| Диспетчер  | Dispatcher Two | dispatcher2@example.com| password |
| Мастер     | Master One     | master1@example.com    | password |
| Мастер     | Master Two     | master2@example.com    | password |

На странице входа есть кнопки быстрого входа под каждую роль для удобства демонстрации.

## Проверка гонки (race condition)

В системе реализована защита от одновременного взятия одной заявки в работу двумя мастерами.  
При параллельных запросах на `POST /api/requests/{id}/take` только первый завершится успешно (статус 200), остальные получат ответ `409 Conflict` с сообщением о том, что заявка уже взята или недоступна.

### Автоматический тест (PHPUnit)

В комплекте идут функциональные тесты, включая специальный тест на гонку:
```bash
docker compose exec backend php artisan test --filter=race_condition_on_take_returns_409_for_second_request
```

### Ручная проверка с помощью скрипта

В корне проекта находится скрипт `race_test.sh`, который имитирует два параллельных запроса.

Подготовка:
1. Войдите как диспетчер и создайте заявку.
2. Назначьте заявку на мастера (например, Мастер 1).
3. Запомните ID заявки.

Запуск:
```bash
./race_test.sh <ID_заявки> <email> <пароль>
```
(по умолчанию используется мастер master1@example.com с паролем password)

Если нужно использовать другого мастера:
```bash
./race_test.sh <ID_заявки> <email> <пароль>
```

Примеры:
```bash
./race_test.sh 25
./race_test.sh 25 master2@example.com password2
```

Ожидаемый результат:
```text
Тестирование гонки для заявки ID=5
Запуск двух параллельных запросов...
Результат 1: HTTP 200
Результат 2: HTTP 409
SUCCESS: Гонка обработана корректно
```

Если вместо этого появляются два раза 200 или другие коды, значит защита не сработала (это возможно только при ошибке в коде).

## Структура проекта

Проект разделён на две основные части: бэкенд (Laravel) и фронтенд (Vue 3), которые работают через API. Вся инфраструктура контейнеризирована с помощью Docker.

```text
├── backend/ # Laravel приложение
│    ├── app/ # Модели, контроллеры, политики, события
│    ├── database/ # Миграции, фабрики, сиды
│    ├── routes/ # API маршруты (web.php, api.php)
│    ├── tests/ # Unit и Feature тесты
│    └── ...
├── frontend/ # Vue 3 приложение (Vite)
│    ├── src/ # Компоненты, страницы, стори (Pinia)
│    ├── tests/ # Unit тесты (Vitest)
│    └── ...
├── docker/ # Dockerfile и конфигурации
│    ├── backend/ # Dockerfile для PHP
│    ├── frontend/ # Dockerfile для Node
│    └── nginx/ # Конфигурация nginx
├── docker-compose.yml # Оркестрация контейнеров
├── race_test.sh # Скрипт для проверки гонки
└── README.md # Документация
```

**Основные технологии:**
- **Backend**: PHP 8.3, Laravel 12, PostgreSQL, Redis, Sanctum, Laravel Queues
- **Frontend**: Vue 3, Vite, Pinia, Vue Router, Axios
- **Инфраструктура**: Docker, Nginx, MySQL (для тестов)

## Тестирование

Проект покрыт автоматическими тестами: модульными (unit) и функциональными (feature) для бэкенда, а также модульными тестами для ключевых компонентов фронтенда.

### Запуск тестов бэкенда

В контейнере `backend` доступны все команды Laravel для тестирования.

```bash
# Запуск всех тестов (unit + feature)
docker compose exec backend php artisan test

# Запуск только модульных тестов
docker compose exec backend php artisan test --testsuite=Unit

# Запуск только функциональных тестов
docker compose exec backend php artisan test --testsuite=Feature

# Запуск конкретного теста (например, на гонку)
docker compose exec backend php artisan test --filter=race_condition_on_take_returns_409_for_second_request
```

## Тестирование с разными базами данных

По умолчанию тесты выполняются на PostgreSQL. Для проверки совместимости с MySQL можно использовать профиль `test` и соответствующий файл окружения.

1. Убедитесь, что контейнер `mysql` запущен. Это можно сделать, запустив сервисы с профилем `test` используя настройки для MySQL из файла `.env.test`:
   ```bash
   docker-compose --profile test --env-file .env.test up -d --build
   ```
   
2. Выполните тесты, используя настройки для MySQL из файла .env.test:
   ```bash
   docker compose --profile test --env-file .env.test exec backend php artisan test
   ```

Если вы хотите временно переключить соединение без использования файла .env.test, можно явно задать все необходимые переменные:
   ```bash
   docker compose exec backend bash -c "DB_CONNECTION=mysql DB_HOST=mysql DB_PORT=3306 DB_DATABASE=repair_test DB_USERNAME=repair_user DB_PASSWORD=secret php artisan test"
   ```

Убедитесь, что база данных repair_test создана и доступна для пользователя repair_user.

## Запуск тестов фронтенда

В контейнере frontend выполните:
   ```bash
   # Установка зависимостей (если не сделано)
   docker compose exec frontend npm install

   # Запуск unit-тестов (Vitest)
   docker compose exec frontend npm run test:unit
   ```

## Дополнительная информация

- **Аудит действий**: все изменения статусов и назначений логируются в таблицу `request_audits` (можно отключить через `FEATURE_AUDIT_LOG` в `.env`).
- **Защита от гонок**: атомарный UPDATE на уровне базы данных гарантирует корректность взятия заявок в работу.
- **Обработка ошибок**: на фронтенде реализован собственный компонент Toast для понятных уведомлений.
- **Принятые архитектурные решения**: подробности в файле [DECISIONS.md](DECISIONS.md).
- **Использование AI**: описание опыта работы с ассистентом в процессе разработки — в файле [PROMPTS.md](PROMPTS.md).

---

Проект выполнен в рамках тестового задания.  
По вопросам обращайтесь к разработчику.