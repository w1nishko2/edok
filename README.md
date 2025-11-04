# IM-EDOK - Сайт рецептов

Laravel-приложение для парсинга и публикации рецептов с сайта 1000.menu.

## 🚀 Основные возможности

- **Автоматический парсинг рецептов** с сайта 1000.menu
- **Очередь обработки** рецептов
- **SEO-оптимизация**: автоматическая генерация sitemap
- **Telegram-бот** для публикации рецептов
- **Адаптивный дизайн** с поддержкой мобильных устройств

## 📋 Доступные команды

### Парсинг рецептов

```bash
# Основная команда парсинга (рекомендуется)
php artisan recipes:parse --count=30

# Сбор URL в очередь
php artisan recipes:collect-urls --count=100

# Обработка очереди
php artisan recipes:process-queue --batch=10

# Статистика очереди
php artisan queue:stats
```

### Системные команды

```bash
# Генерация sitemap
php artisan sitemap:generate

# Проверка базы данных
php artisan db:check

# Отладка парсера
php artisan parser:debug
```

### Telegram

```bash
# Публикация рецепта в Telegram
php artisan telegram:publish {recipe_id}

# Публикация подборки
php artisan telegram:publish-collection
```

## ⚙️ Автоматизация (Cron)

Расписание в `app/Console/Kernel.php`:

- **Каждые 30 минут**: парсинг 42 рецептов
- **Каждые 2 часа**: обновление sitemap

## 📁 Структура проекта

```
app/
├── Console/Commands/     # Artisan команды
├── Http/Controllers/     # Контроллеры
├── Models/              # Модели данных
├── Services/            # Бизнес-логика
│   ├── RecipeParserService.php
│   ├── RecipeListParserService.php
│   ├── TelegramService.php
│   ├── SeoService.php
│   └── SitemapService.php
└── Observers/           # Наблюдатели моделей
```

## 🛠️ Установка

```bash
# Установка зависимостей
composer install
npm install

# Настройка окружения
cp .env.example .env
php artisan key:generate

# Миграции
php artisan migrate

# Сборка фронтенда
npm run build
```

## 📊 Технологии

- **Backend**: Laravel 10
- **Frontend**: Vue.js 3 + Vite
- **База данных**: MySQL
- **Парсинг**: Guzzle + DOMDocument
- **API**: Telegram Bot API

## 📝 Лицензия

Проект разработан для образовательных целей.


## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# imedok
