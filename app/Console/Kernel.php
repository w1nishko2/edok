<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // НОВАЯ СХЕМА РАБОТЫ (2 отдельных процесса):
        
        // 1. Сбор URL рецептов - легкая задача каждые 15 минут
        // Собирает URLs и добавляет в очередь recipe_queue
        $schedule->command('recipes:collect-urls --count=30')
            ->everyFifteenMinutes()
            ->appendOutputTo(storage_path('logs/url-collector.log'));

        // 2. Обработка очереди - тяжелая задача каждые 30 минут
        // Парсит полные данные рецептов (50 штук за раз)
        $schedule->command('recipes:process-queue --limit=50')
            ->everyThirtyMinutes()
            ->appendOutputTo(storage_path('logs/queue-processor.log'));

        // 3. Обновление sitemap каждые 2 часа
        $schedule->command('sitemap:generate')
            ->everyTwoHours()
            ->appendOutputTo(storage_path('logs/sitemap.log'));
            
        // ПРИМЕЧАНИЕ: Для ПРОДАКШН используйте CRONTAB (см. PRODUCTION_CRONTAB_NEW.txt)
        // Там настроен бесконечный сборщик + обработка по 50 рецептов каждые 30 мин
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
