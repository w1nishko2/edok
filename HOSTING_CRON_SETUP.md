# 🚀 УСТАНОВКА CRON НА ХОСТИНГЕ

## Шаг 1: Узнайте путь к проекту

Подключитесь по SSH и выполните:
```bash
pwd
# Результат будет примерно: /home/username/domains/imedok.ru/public_html
```

Запомните этот путь!

---

## Шаг 2: Проверьте путь к PHP

```bash
which php
# или
php -v
```

Если нужна конкретная версия:
```bash
/usr/bin/php8.2 -v
```

---

## Шаг 3: Откройте редактор crontab

```bash
crontab -e
```

---

## Шаг 4: Добавьте команды cron

Скопируйте и вставьте (замените пути на свои):

```bash
# ============================================
# CRON для imedok.ru
# ============================================

# ПАРСИНГ РЕЦЕПТОВ - каждые 30 минут (2016 рецептов/день)
*/30 * * * * cd /home/username/domains/imedok.ru/public_html && php artisan recipes:parse --count=42 >> storage/logs/parser.log 2>&1

# ОБРАБОТКА ОЧЕРЕДИ - каждые 10 минут
*/10 * * * * cd /home/username/domains/imedok.ru/public_html && php artisan recipes:process-queue >> storage/logs/queue.log 2>&1

# ГЕНЕРАЦИЯ SITEMAP - каждые 2 часа
0 */2 * * * cd /home/username/domains/imedok.ru/public_html && php artisan sitemap:generate >> storage/logs/sitemap.log 2>&1

# ПУБЛИКАЦИЯ В TELEGRAM - 4 раза в день (9:00, 15:00, 21:00, 03:00)
0 9,15,21,3 * * * cd /home/username/domains/imedok.ru/public_html && php artisan recipes:publish-to-telegram >> storage/logs/telegram.log 2>&1

# ПУБЛИКАЦИЯ ПОДБОРОК - раз в день в 12:00
0 12 * * * cd /home/username/domains/imedok.ru/public_html && php artisan recipes:publish-collection >> storage/logs/telegram-collections.log 2>&1

# ОЧИСТКА СТАРЫХ ЛОГОВ - каждый понедельник в 3:00 утра
0 3 * * 1 cd /home/username/domains/imedok.ru/public_html && find storage/logs -name "*.log" -type f -mtime +30 -delete

# ОЧИСТКА КЕША - каждый день в 4:00 утра
0 4 * * * cd /home/username/domains/imedok.ru/public_html && php artisan cache:clear >> /dev/null 2>&1
```

---

## Шаг 5: Сохраните и выйдите

- В **nano**: Ctrl+X, потом Y, потом Enter
- В **vim**: нажмите Esc, потом :wq и Enter

---

## Шаг 6: Проверьте установку

```bash
crontab -l
```

Должны увидеть все добавленные задачи.

---

## 📊 Расписание выполнения

| Команда | Частота | Время | Нагрузка |
|---------|---------|-------|----------|
| recipes:parse | Каждые 30 минут | 00:00, 00:30, 01:00... | 48 раз/день |
| recipes:process-queue | Каждые 10 минут | 00:00, 00:10, 00:20... | 144 раз/день |
| sitemap:generate | Каждые 2 часа | 00:00, 02:00, 04:00... | 12 раз/день |
| publish-to-telegram | 4 раза в день | 09:00, 15:00, 21:00, 03:00 | 4 раз/день |
| publish-collection | 1 раз в день | 12:00 | 1 раз/день |
| Очистка логов | Раз в неделю | Пн 03:00 | 1 раз/неделю |
| Очистка кеша | Каждый день | 04:00 | 1 раз/день |

---

## 🔍 Мониторинг и отладка

### Проверить логи парсера:
```bash
tail -f storage/logs/parser.log
```

### Проверить логи очереди:
```bash
tail -f storage/logs/queue.log
```

### Проверить логи Telegram:
```bash
tail -f storage/logs/telegram.log
```

### Проверить все логи Laravel:
```bash
tail -f storage/logs/laravel.log
```

### Проверить сколько места занимают логи:
```bash
du -sh storage/logs/
```

### Очистить все логи вручную:
```bash
cd storage/logs && rm *.log
```

---

## ⚠️ Важные замечания

1. **Путь к проекту**: Замените `/home/username/domains/imedok.ru/public_html` на реальный путь
2. **Версия PHP**: Если на хостинге несколько версий PHP, укажите полный путь: `/usr/bin/php8.2`
3. **Права доступа**: Убедитесь, что папка `storage/logs` доступна для записи (chmod 775)
4. **Логи**: Cron пишет в `storage/logs/`, не забывайте чистить старые логи
5. **Telegram**: Убедитесь, что настроен токен бота в `.env`

---

## 🛠 Альтернатива: Laravel Scheduler

Если хостинг поддерживает, можно использовать один cron:

```bash
* * * * * cd /home/username/domains/imedok.ru/public_html && php artisan schedule:run >> /dev/null 2>&1
```

И настроить всё в `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('recipes:parse --count=42')
        ->everyThirtyMinutes()
        ->appendOutputTo(storage_path('logs/parser.log'));

    $schedule->command('recipes:process-queue')
        ->everyTenMinutes()
        ->appendOutputTo(storage_path('logs/queue.log'));

    $schedule->command('sitemap:generate')
        ->everyTwoHours()
        ->appendOutputTo(storage_path('logs/sitemap.log'));

    $schedule->command('recipes:publish-to-telegram')
        ->dailyAt('09:00')
        ->appendOutputTo(storage_path('logs/telegram.log'));

    $schedule->command('recipes:publish-to-telegram')
        ->dailyAt('15:00')
        ->appendOutputTo(storage_path('logs/telegram.log'));

    $schedule->command('recipes:publish-to-telegram')
        ->dailyAt('21:00')
        ->appendOutputTo(storage_path('logs/telegram.log'));

    $schedule->command('recipes:publish-to-telegram')
        ->dailyAt('03:00')
        ->appendOutputTo(storage_path('logs/telegram.log'));

    $schedule->command('recipes:publish-collection')
        ->dailyAt('12:00')
        ->appendOutputTo(storage_path('logs/telegram-collections.log'));

    $schedule->command('cache:clear')
        ->dailyAt('04:00');
}
```

---

## ✅ Готово!

После настройки cron автоматически будет:
- Парсить рецепты каждые 30 минут
- Обрабатывать очередь каждые 10 минут
- Генерировать sitemap каждые 2 часа
- Публиковать в Telegram 4 раза в день
- Чистить старые логи каждую неделю
