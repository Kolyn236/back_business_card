# Feedback Backend

Backend-репозиторий на Symfony 7 для формы "Оффер / проект" с сайта-визитки.

## Docker

Проект теперь рассчитан на две схемы запуска:

- `compose.yaml` содержит базовый production-like стек: `nginx`, `php-fpm`, `worker`, `redis`.
- `compose.override.yaml` автоматически подключается для локальной разработки и добавляет bind-mount исходников, `Mailpit` и dev-настройки Symfony.

### Локальная разработка

1. При необходимости скопируйте `.env.example` в `.env.local` и задайте значения для `TELEGRAM_*` и email-полей.
2. Поднимите стек:

```bash
docker compose up --build -d
```

Что поднимется:

- API: `http://localhost:8080`
- Mailpit UI: `http://localhost:8025`
- `worker` для `Messenger`
- `redis` для `async`, `failed` transport и `cache.app`

Полезные команды:

```bash
docker compose logs -f app
docker compose logs -f worker
docker compose exec app php bin/console about
docker compose exec app php bin/console messenger:stats
```

Если изменился `composer.json` или `composer.lock`, выполните:

```bash
docker compose exec app composer install
```

### Production / deploy

Для выката используйте только базовый файл `compose.yaml`, без dev-override:

```bash
docker compose -f compose.yaml build
docker compose -f compose.yaml up -d
```

Перед запуском задайте production-значения минимум для этих переменных:

```dotenv
DOCKER_APP_ENV=prod
DOCKER_APP_DEBUG=0
APP_SECRET=change-me
DOCKER_DEFAULT_URI=https://api.example.com
DOCKER_HTTP_PORT=80
DOCKER_MAILER_DSN=smtp://user:pass@smtp.example.com:587
DOCKER_MESSENGER_TRANSPORT_DSN=redis://redis:6379/messages
DOCKER_MESSENGER_FAILED_TRANSPORT_DSN=redis://redis:6379/failed_messages
DOCKER_CACHE_APP_DSN=redis://redis:6379/cache
TELEGRAM_BOT_TOKEN=123456:token
TELEGRAM_CHAT_ID=-1001234567890
FEEDBACK_MAIL_FROM=no-reply@example.com
FEEDBACK_MAIL_FROM_NAME="Business Card Backend"
FEEDBACK_MAIL_TO=owner@example.com
FEEDBACK_MAIL_ENVELOPE_SENDER=no-reply@example.com
```

В production-схеме:

- `nginx` принимает HTTP-трафик и проксирует PHP-запросы в `app:9000`
- `app` работает как `php-fpm`
- `worker` обрабатывает асинхронные email и Telegram notifications
- `redis` хранит очереди Messenger и ключи дедупликации обработки feedback

## Что делает

- Принимает `POST /api/feedback` с JSON-телом.
- Маппит запрос в DTO.
- Валидирует DTO через Symfony Validator.
- После успешной валидации ставит уведомления в Telegram и email в очередь Messenger.
- Возвращает JSON-ответ и JSON-ошибки.

## Структура каталогов

```text
.
├── config/
├── public/
├── src/
│   ├── Feedback/
│   │   ├── Application/
│   │   │   ├── Dto/
│   │   │   ├── Factory/
│   │   │   ├── Port/
│   │   │   └── Service/
│   │   ├── Infrastructure/
│   │   │   ├── Config/
│   │   │   └── Notification/
│   │   └── Presentation/
│   │       └── Http/
│   └── Shared/
│       ├── Application/
│       └── Presentation/
├── tests/
└── composer.json
```

## Поток обработки

1. Controller принимает HTTP-запрос и делегирует разбор тела в `FeedbackRequestMapper`.
2. `FeedbackSubmissionFactory` создаёт DTO `FeedbackSubmission`.
3. `SubmitFeedbackService` валидирует DTO и запускает набор `FeedbackSubmissionProcessorInterface`.
4. Инфраструктурные процессоры ставят Telegram и email в очередь Messenger, не прерывая цепочку на первом сбое.
5. `ApiExceptionSubscriber` приводит ошибки к JSON-формату.

## Feedback API

### Endpoint

`POST /api/feedback`

Назначение: принять данные формы "Оффер / проект", провалидировать их, поставить уведомления в Telegram и email в очередь, вернуть JSON-ответ.

### Request

#### Headers

```http
Content-Type: application/json
Accept: application/json
```

#### JSON body

```json
{
  "name": "Nikola Tesla",
  "email": "nikola@example.com",
  "company": "Acme Studio",
  "offerType": "Landing page",
  "budget": "$2k-$5k",
  "message": "Нужен сайт-визитка под запуск нового продукта.",
  "timeline": "2-3 weeks",
  "consent": true
}
```

### Fields

| Field | Type | Required | Description |
|---|---|---:|---|
| `name` | `string` | Yes | Имя отправителя |
| `email` | `string` | Yes | Email для обратной связи |
| `company` | `string \| null` | No | Компания / бренд |
| `offerType` | `string` | Yes | Тип запроса |
| `budget` | `string \| null` | No | Бюджет в свободной форме |
| `message` | `string` | Yes | Описание задачи |
| `timeline` | `string \| null` | No | Желаемый срок |
| `consent` | `boolean` | Yes | Согласие на обработку данных, должно быть `true` |

### Validation Rules

- `name`: required, string, trimmed non-empty, max length `120`
- `email`: required, string, trimmed non-empty, valid email, max length `180`
- `company`: optional, string if present, max length `180`
- `offerType`: required, string, trimmed non-empty, max length `100`
- `budget`: optional, string if present, max length `100`
- `message`: required, string, trimmed non-empty, min length `10`, max length `5000`
- `timeline`: optional, string if present, max length `100`
- `consent`: required, boolean, must be `true`

### Successful Request Example

```bash
curl -X POST http://localhost:8000/api/feedback \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "name": "Nikola Tesla",
    "email": "nikola@example.com",
    "company": "Acme Studio",
    "offerType": "Landing page",
    "budget": "$2k-$5k",
    "message": "Нужен сайт-визитка под запуск нового продукта.",
    "timeline": "2-3 weeks",
    "consent": true
  }'
```

### Successful Response

Status:

```http
200 OK
```

Body:

```json
{
  "status": "ok",
  "message": "Feedback submitted successfully."
}
```

### Error Format

All API errors use this JSON shape:

```json
{
  "status": "error",
  "code": "validation_failed",
  "message": "Validation failed.",
  "errors": {
    "fieldName": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

For non-validation errors, `errors` may be omitted.

### Validation Error Example

Status:

```http
422 Unprocessable Entity
```

Body:

```json
{
  "status": "error",
  "code": "validation_failed",
  "message": "Validation failed.",
  "errors": {
    "email": [
      "This value is not a valid email address."
    ],
    "message": [
      "This value is too short. It should have 10 characters or more."
    ]
  }
}
```

### Invalid Request Examples

If request body is not valid JSON:

Status:

```http
400 Bad Request
```

```json
{
  "status": "error",
  "code": "invalid_request",
  "message": "Request body must contain valid JSON."
}
```

If `Content-Type` is not `application/json`:

Status:

```http
415 Unsupported Media Type
```

```json
{
  "status": "error",
  "code": "unsupported_media_type",
  "message": "Content-Type must be application/json."
}
```

If payload has invalid primitive types:

```json
{
  "status": "error",
  "code": "invalid_request",
  "message": "consent: Field must be a boolean."
}
```

## Email Delivery

Email-уведомления отправляются через Symfony Mailer, а SMTP-доставка выполняется асинхронно через Messenger.

Нужные переменные окружения:

```dotenv
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
MESSENGER_FAILED_TRANSPORT_DSN=redis://localhost:6379/failed_messages
FEEDBACK_MAIL_FROM=no-reply@example.com
FEEDBACK_MAIL_FROM_NAME="Business Card Backend"
FEEDBACK_MAIL_TO=owner@example.com
FEEDBACK_MAIL_ENVELOPE_SENDER=no-reply@example.com
```

- `FEEDBACK_MAIL_FROM` задаёт header `From`.
- `FEEDBACK_MAIL_ENVELOPE_SENDER` задаёт SMTP envelope sender (`MAIL FROM`) и нужен для провайдеров, которые принимают письма только от верифицированного sender.
- `Reply-To` выставляется в email отправителя формы, чтобы можно было отвечать напрямую на заявку.

Для обработки очереди запустите worker:

```bash
php bin/console messenger:consume async -vv
```

Если SMTP временно недоступен, Messenger повторит доставку email до 5 раз с exponential backoff. После исчерпания ретраев сообщение уйдёт в `failed` transport, а ошибка будет записана в mailer-лог.

## Telegram Delivery

Telegram-уведомление ставится в Messenger и затем отправляется worker'ом через Bot API.

- Нужны переменные `TELEGRAM_BOT_TOKEN` и `TELEGRAM_CHAT_ID`.
- Telegram использует тот же `async` transport и ту же retry strategy Messenger, что и email.
- Невосстановимые ошибки Telegram API (`4xx`, кроме `429`) помечаются как permanent failure без лишних ретраев.
- Ошибки и ретраи пишутся в отдельный `telegram` log channel.
- Длинный текст заявки автоматически усекается перед отправкой, чтобы не упереться в лимит сообщения Telegram.

### Notification Delivery Error Example

If validation passed but Telegram or email message could not be queued:

Status:

```http
502 Bad Gateway
```

```json
{
  "status": "error",
  "code": "notification_delivery_failed",
  "message": "Telegram notification could not be queued."
}
```

### HTTP Status Codes

- `200 OK`: request accepted and processed successfully
- `400 Bad Request`: malformed JSON, invalid payload shape, wrong primitive field types
- `415 Unsupported Media Type`: request is not `application/json`
- `422 Unprocessable Entity`: field validation errors
- `502 Bad Gateway`: notification queueing failed after successful validation
- `500 Internal Server Error`: unexpected server-side error

### Content-Type

Expected request content type:

```http
Content-Type: application/json
Accept: application/json
```

Response content type:

```http
Content-Type: application/json; charset=utf-8
```

## Переменные окружения

```dotenv
APP_ENV=dev
APP_SECRET=change-me
DEFAULT_URI=http://localhost
MAILER_DSN=smtp://user:pass@smtp.example.com:587
TELEGRAM_BOT_TOKEN=123456:token
TELEGRAM_CHAT_ID=-1001234567890
FEEDBACK_MAIL_FROM=no-reply@example.com
FEEDBACK_MAIL_TO=owner@example.com
```

## Почему так

- DTO + отдельный mapper: контроллер не знает про детали JSON и нормализацию полей.
- `SubmitFeedbackService` оркестрирует use case и остаётся точкой расширения для anti-spam, очередей, логирования и сохранения в БД.
- `FeedbackSubmissionProcessorInterface`: Telegram и email изолированы как инфраструктурные адаптеры; потом сюда же можно добавить persistence или audit-обработчики.
- `ApiExceptionSubscriber`: единый JSON-формат ошибок без дублирования по контроллерам.

## Локальный запуск

```bash
composer install
php -S 127.0.0.1:8000 -t public
```

Для production/dev-сервера лучше использовать normal web server или PHP-FPM + nginx.
