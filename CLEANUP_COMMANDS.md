# Оптимизация команд Laravel

## 📋 Анализ текущих команд

### ✅ РАБОЧИЕ КОМАНДЫ (оставляем)

1. **ParseRecipesCommand** (`recipes:parse`)
   - Основной парсинг рецептов
   - Используется в cron каждые 30 минут
   - ✅ КРИТИЧЕСКИ ВАЖНАЯ

2. **ProcessRecipeQueue** (`recipes:process-queue`)
   - Обработка очереди рецептов
   - Используется в cron каждые 10 минут
   - ✅ КРИТИЧЕСКИ ВАЖНАЯ

3. **GenerateSitemap** (`sitemap:generate`)
   - Генерация sitemap для SEO
   - Используется в cron каждые 2 часа
   - ✅ КРИТИЧЕСКИ ВАЖНАЯ

4. **PublishRecipeToTelegram** (`recipes:publish-to-telegram`)
   - Публикация рецептов в Telegram
   - Используется в cron каждые 6 часов
   - ✅ ВАЖНАЯ

5. **PublishRecipeCollection** (`recipes:publish-collection`)
   - Публикация подборок в Telegram
   - Используется в cron раз в день
   - ✅ ВАЖНАЯ

6. **CollectRecipeUrls** (`recipes:collect-urls`)
   - Сбор URL рецептов
   - Может использоваться вручную
   - ✅ ПОЛЕЗНАЯ

---

### ❌ КОМАНДЫ ДЛЯ УДАЛЕНИЯ (для разработки)

1. **CheckDatabaseCommand** (`db:check`)
   - Назначение: Быстрая проверка базы данных
   - Причина удаления: Только для отладки, не нужна на проде
   - Файл: `app/Console/Commands/CheckDatabaseCommand.php`

2. **DebugParserCommand** (`parser:debug`)
   - Назначение: Отладка парсера с детальным выводом
   - Причина удаления: Debug-команда, не для продакшена
   - Файл: `app/Console/Commands/DebugParserCommand.php`

3. **TestSearchCommand** (`test:search`)
   - Назначение: Тестирование поиска рецептов
   - Причина удаления: Только для тестирования
   - Файл: `app/Console/Commands/TestSearchCommand.php`

4. **RecipeQueueStats** (`recipes:queue-stats`)
   - Назначение: Показ статистики очереди
   - Причина удаления: Мониторинг, можно делать через админку
   - Файл: `app/Console/Commands/RecipeQueueStats.php`

5. **ParseInfiniteScroll** (`parse-infinite-scroll`)
   - Назначение: Парсинг с бесконечной прокруткой
   - Причина удаления: Устарел, используем ParseRecipesCommand
   - Файл: `app/Console/Commands/ParseInfiniteScroll.php`

---

## 🗑️ Команды для удаления файлов

```bash
# Удаляем команды для разработки
rm app/Console/Commands/CheckDatabaseCommand.php
rm app/Console/Commands/DebugParserCommand.php
rm app/Console/Commands/TestSearchCommand.php
rm app/Console/Commands/RecipeQueueStats.php
rm app/Console/Commands/ParseInfiniteScroll.php
```

---

## 📊 Итоговая структура команд

После очистки останется **6 команд**:

```
app/Console/Commands/
├── CollectRecipeUrls.php          # Сбор URL (ручная)
├── GenerateSitemap.php            # Генерация sitemap (cron)
├── ParseRecipesCommand.php        # Основной парсинг (cron)
├── ProcessRecipeQueue.php         # Обработка очереди (cron)
├── PublishRecipeCollection.php    # Подборки Telegram (cron)
└── PublishRecipeToTelegram.php    # Публикация Telegram (cron)
```

---

## 📝 Обновленный Kernel.php

После удаления команд, файл `app/Console/Kernel.php` остается без изменений:

```php
protected function schedule(Schedule $schedule): void
{
    // Парсинг каждые 30 минут по 42 рецепта = 2016 рецептов/день
    $schedule->command('recipes:parse --count=42')
        ->everyThirtyMinutes()
        ->appendOutputTo(storage_path('logs/parser.log'));

    // Обновление sitemap каждые 2 часа
    $schedule->command('sitemap:generate')
        ->everyTwoHours()
        ->appendOutputTo(storage_path('logs/sitemap.log'));
}
```

---

## ✅ Преимущества после очистки

1. **Меньше файлов** - проще поддерживать
2. **Нет путаницы** - только рабочие команды
3. **Быстрее загрузка** - меньше классов для автозагрузки
4. **Понятная структура** - каждая команда имеет цель

---

## 🎯 PRODUCTION CRONTAB

Смотрите файл `PRODUCTION_CRONTAB.txt` с готовыми настройками cron.
