# Alex Portfolio / Vue Business Card

SPA-шаблон персонального сайта backend-разработчика на Vue 3 + Vite. Проект рассчитан на
сценарий "портфолио + контакты + входящие офферы/проекты" и подходит как стартовая база под
личный сайт, мини-лендинг специалиста или аккуратную developer business card.

## Что внутри

- Vue 3 + Vite
- Vue Router
- SCSS
- простые переиспользуемые UI-компоненты
- единый контентный конфиг для страниц и блоков
- SEO/meta для маршрутов
- страница `Feedback` для офферов и проектов
- composable для формы
- сервисный слой для отправки формы

## Быстрый старт

```bash
npm install
cp .env.example .env
npm run dev
```

В монорепо dev-сервер фронта живёт в `frontend/`, а production build складывается в
`../backend/public/app` и потом отдаётся nginx по `/app`.

## Сборка

```bash
npm run build
```

## Настройка формы

По умолчанию фронт отправляет запросы в backend endpoint:

`POST /api/feedback`

Пример payload:

```json
{
  "name": "Иван",
  "email": "ivan@example.com",
  "company": "Acme",
  "offerType": "project",
  "budget": "2000-3000 EUR",
  "message": "Нужен API и интеграции",
  "timeline": "2 месяца",
  "consent": true
}
```

## Что можно доработать

- подменить шаблонные кейсы на реальные цифры и достижения
- отправка в Telegram и email через backend
- honeypot / rate limit / captcha
- schema.org / JSON-LD для профиля и контактов
