#!/bin/bash
# Скрипт для запуска Laravel Scheduler на web-хостинге
# Добавьте в crontab: */1 * * * * /home/g/gamechann2/im-edok_ru/schedule.sh

# Переходим в директорию проекта
cd /home/g/gamechann2/im-edok_ru || exit

# Запускаем планировщик Laravel с PHP 8.1
php8.1 artisan schedule:run >> /dev/null 2>&1
