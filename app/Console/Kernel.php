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
        // Парсинг каждые 30 минут по 42 рецепта = ровно 2016 рецептов в сутки
        // 48 запусков в день × 42 рецепта = 2016 рецептов
        $schedule->command('recipes:parse --count=42')
            ->everyThirtyMinutes()
            ->appendOutputTo(storage_path('logs/parser.log'));

        // Обновление sitemap каждые 2 часа
        $schedule->command('sitemap:generate')
            ->everyTwoHours()
            ->appendOutputTo(storage_path('logs/sitemap.log'));
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
