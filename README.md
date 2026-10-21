# Back Business Card

Русская версия ниже. English version follows after it.

## RU

Современный монорепозиторий для персонального сайта-визитки backend-разработчика.

Проект состоит из двух частей:

- `frontend/` - SPA на Vue 3 + Vite
- `backend/` - API на Symfony 7.4 для формы обратной связи

Фронтенд собирается в [`backend/public/app`](backend/public/app), а backend принимает запросы на [`POST /api/feedback`](backend/src/Feedback/Presentation/Http/Controller/SubmitFeedbackController.php#L16).

### Что умеет проект

- Показывает личный сайт с разделами `Главная`, `Обо мне`, `Проекты`, `Оффер / проект`, `Контакты`
- Принимает заявки и сообщения через JSON API
- Валидирует входящие данные
- Отправляет уведомления в Telegram и по email
- Обрабатывает уведомления асинхронно через Symfony Messenger + Redis
- Поддерживает локальную разработку через Docker Compose

### Стек

- Frontend: Vue 3, Vue Router, Vite, SCSS, Axios
- Backend: PHP 8.2+, Symfony 7.4, Validator, Mailer, Messenger, Monolog
- Infra: Docker Compose, Nginx, Redis, Mailpit

### Структура репозитория

```text
.
├── backend/      # Symfony API и обработка feedback
├── frontend/     # Vue SPA
├── deploy/       # deploy-шаблоны и документация
├── docker-compose.yml
└── docker-compose.override.yml
```

### Как поднять проект через Docker

Требования:

- Docker
- Docker Compose

1. Подготовьте переменные окружения backend:

```bash
cp backend/.env.example backend/.env.local
```

Минимально заполните в `backend/.env.local`:

- `APP_SECRET`
- `TELEGRAM_BOT_TOKEN`
- `TELEGRAM_CHAT_ID`
- `FEEDBACK_MAIL_FROM`
- `FEEDBACK_MAIL_TO`
- `FEEDBACK_MAIL_ENVELOPE_SENDER`

2. Подготовьте переменные окружения frontend:

```bash
cp frontend/.env.example frontend/.env
```

3. Поднимите контейнеры:

```bash
docker compose up --build -d
```

4. При первом запуске установите PHP-зависимости внутри контейнера:

```bash
docker compose exec app composer install
```

После запуска будут доступны:

- сайт и API: `http://localhost:8080`
- Mailpit UI: `http://localhost:8025`
- Redis: внутри docker-сети

Полезные команды:

```bash
docker compose logs -f app
docker compose logs -f worker
docker compose exec app php bin/console about
docker compose exec app php bin/console messenger:stats
```

### Локальный запуск без полного Docker-цикла

Такой режим удобен, если frontend хочется запускать отдельно через Vite.

Backend:

```bash
docker compose up --build -d
docker compose exec app composer install
```

Frontend:

```bash
cd frontend
npm install
npm run dev
```

В этом режиме Vite dev server будет доступен на `http://localhost:5173`, а API останется на `http://localhost:8080/api`.

### Production build

Перед production-запуском нужно собрать frontend:

```bash
cd frontend
npm ci
npm run build
cd ..
docker compose -f docker-compose.yml build
docker compose -f docker-compose.yml up -d
```

Сборка фронтенда попадает в [`backend/public/app`](backend/public/app), откуда её раздаёт Nginx.

### API формы

Endpoint:

```http
POST /api/feedback
Content-Type: application/json
Accept: application/json
```

Пример тела запроса:

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

Успешный ответ:

```json
{
  "status": "ok",
  "message": "Feedback submitted successfully."
}
```

### Тесты и проверка

Backend:

```bash
docker compose exec app php bin/phpunit
```

Frontend:

```bash
cd frontend
npm run lint
npm run build
```

### Дополнительные материалы

- backend details: [backend/README.md](backend/README.md)
- frontend details: [frontend/README.md](frontend/README.md)
- deployment notes: [deploy/README.md](deploy/README.md)

---

## EN

A modern monorepo for a personal backend developer business card website.

The project has two main parts:

- `frontend/` - a Vue 3 + Vite SPA
- `backend/` - a Symfony 7.4 API for the contact/feedback form

The frontend build is generated into [`backend/public/app`](backend/public/app), while the backend handles requests at [`POST /api/feedback`](backend/src/Feedback/Presentation/Http/Controller/SubmitFeedbackController.php#L16).

### What the project does

- Serves a personal website with `Home`, `About`, `Projects`, `Offer / Project`, and `Contacts` pages
- Accepts incoming requests through a JSON API
- Validates submitted form data
- Sends notifications to Telegram and email
- Processes notifications asynchronously via Symfony Messenger + Redis
- Supports local development with Docker Compose

### Stack

- Frontend: Vue 3, Vue Router, Vite, SCSS, Axios
- Backend: PHP 8.2+, Symfony 7.4, Validator, Mailer, Messenger, Monolog
- Infra: Docker Compose, Nginx, Redis, Mailpit

### Repository structure

```text
.
├── backend/      # Symfony API and feedback processing
├── frontend/     # Vue SPA
├── deploy/       # deploy templates and docs
├── docker-compose.yml
└── docker-compose.override.yml
```

### How to run the project with Docker

Requirements:

- Docker
- Docker Compose

1. Prepare backend environment variables:

```bash
cp backend/.env.example backend/.env.local
```

At minimum, set these values in `backend/.env.local`:

- `APP_SECRET`
- `TELEGRAM_BOT_TOKEN`
- `TELEGRAM_CHAT_ID`
- `FEEDBACK_MAIL_FROM`
- `FEEDBACK_MAIL_TO`
- `FEEDBACK_MAIL_ENVELOPE_SENDER`

2. Prepare frontend environment variables:

```bash
cp frontend/.env.example frontend/.env
```

3. Start the containers:

```bash
docker compose up --build -d
```

4. On first start, install PHP dependencies inside the app container:

```bash
docker compose exec app composer install
```

After startup, these services are available:

- website and API: `http://localhost:8080`
- Mailpit UI: `http://localhost:8025`
- Redis: inside the Docker network

Useful commands:

```bash
docker compose logs -f app
docker compose logs -f worker
docker compose exec app php bin/console about
docker compose exec app php bin/console messenger:stats
```

### Local development with Vite

This mode is handy if you want to run the frontend separately.

Backend:

```bash
docker compose up --build -d
docker compose exec app composer install
```

Frontend:

```bash
cd frontend
npm install
npm run dev
```

In this mode, the Vite dev server runs at `http://localhost:5173`, while the API remains available at `http://localhost:8080/api`.

### Production build

Before a production run, build the frontend first:

```bash
cd frontend
npm ci
npm run build
cd ..
docker compose -f docker-compose.yml build
docker compose -f docker-compose.yml up -d
```

The frontend output is placed into [`backend/public/app`](backend/public/app) and then served by Nginx.

### Feedback API

Endpoint:

```http
POST /api/feedback
Content-Type: application/json
Accept: application/json
```

Example request body:

```json
{
  "name": "Nikola Tesla",
  "email": "nikola@example.com",
  "company": "Acme Studio",
  "offerType": "Landing page",
  "budget": "$2k-$5k",
  "message": "Need a business card website for a new product launch.",
  "timeline": "2-3 weeks",
  "consent": true
}
```

Success response:

```json
{
  "status": "ok",
  "message": "Feedback submitted successfully."
}
```

### Tests and checks

Backend:

```bash
docker compose exec app php bin/phpunit
```

Frontend:

```bash
cd frontend
npm run lint
npm run build
```

### Extra docs

- backend details: [backend/README.md](backend/README.md)
- frontend details: [frontend/README.md](frontend/README.md)
- deployment notes: [deploy/README.md](deploy/README.md)
