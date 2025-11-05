<?php

namespace App\Console\Commands;

use App\Models\RecipeQueue;
use App\Services\RecipeListParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CollectRecipeUrls extends Command
{
    protected $signature = 'recipes:collect-urls 
                            {category? : Категория для парсинга (meat, fish, ptica и т.д.)}
                            {--count=30 : Количество URL для сбора}
                            {--start-page=1 : Начальная страница (для meat можно начать с 2)}';

    protected $description = 'Сбор URL рецептов с povar.ru и добавление в очередь (легкая задача, каждые 15 мин)';

    public function handle(RecipeListParserService $parser): int
    {
        // Увеличиваем лимит времени выполнения для больших объёмов
        set_time_limit(0); // Без ограничения времени
        ini_set('memory_limit', '512M'); // Увеличиваем лимит памяти
        
        $category = $this->argument('category');
        $targetCount = (int) $this->option('count');
        $startPage = (int) $this->option('start-page');

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   📥 Сбор URL рецептов с Food.ru                     ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        // Если категория не указана, собираем из всех
        if (!$category) {
            $categories = $parser->getCategories();
            $this->info("📋 Будут обработаны все категории:");
            foreach ($categories as $slug => $name) {
                $this->info("  • {$slug} - {$name}");
            }
            $this->newLine();
            
            $totalAdded = 0;
            $totalSkipped = 0;
            
            foreach (array_keys($categories) as $slug) {
                $this->info("🔍 Обрабатываем категорию: {$slug}");
                
                // Колбэк для добавления в очередь сразу после парсинга каждой страницы
                $callback = function($urls, $pageNum) use (&$totalAdded, &$totalSkipped) {
                    foreach ($urls as $url) {
                        try {
                            $exists = RecipeQueue::where('url', $url)->exists();
                            
                            if (!$exists) {
                                RecipeQueue::create([
                                    'url' => $url,
                                    'status' => RecipeQueue::STATUS_PENDING,
                                ]);
                                $totalAdded++;
                            } else {
                                $totalSkipped++;
                            }
                        } catch (\Exception $e) {
                            Log::error("❌ Ошибка добавления URL: {$url}", ['error' => $e->getMessage()]);
                            $totalSkipped++;
                        }
                    }
                    
                    $this->info("  💾 Страница {$pageNum}: добавлено в очередь: " . count($urls));
                };
                
                $parser->parseMultiplePages($slug, $targetCount, $startPage, $callback);
                
                sleep(rand(2, 4)); // Пауза между категориями
            }
            
            $this->newLine();
            $this->info("✅ Всего добавлено: {$totalAdded}");
            $this->info("⏭️  Всего пропущено (дубликаты): {$totalSkipped}");
            
        } else {
            $this->info("🎯 Категория: {$category}");
            $this->info("🎯 Цель: {$targetCount} новых URL");
            $this->info("📄 Начальная страница: {$startPage}");
            $this->newLine();

            $added = 0;
            $skipped = 0;
            
            // Колбэк для добавления в очередь сразу после парсинга каждой страницы
            $callback = function($urls, $pageNum) use (&$added, &$skipped) {
                foreach ($urls as $url) {
                    try {
                        $exists = RecipeQueue::where('url', $url)->exists();
                        
                        if (!$exists) {
                            RecipeQueue::create([
                                'url' => $url,
                                'status' => RecipeQueue::STATUS_PENDING,
                            ]);
                            $added++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Exception $e) {
                        Log::error("❌ Ошибка добавления URL: {$url}", ['error' => $e->getMessage()]);
                        $skipped++;
                    }
                }
                
                $this->info("💾 Страница {$pageNum}: добавлено в очередь: " . count($urls) . " (всего: {$added})");
            };

            $parser->parseMultiplePages($category, $targetCount, $startPage, $callback);
            
            $this->newLine();
            $this->info("✅ Добавлено в очередь: {$added}");
            $this->info("⏭️  Пропущено (дубликаты): {$skipped}");
        }
        
        // Статистика очереди
        $pending = RecipeQueue::where('status', RecipeQueue::STATUS_PENDING)->count();
        
        $this->newLine();
        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   ✅ Сбор URL завершен                                ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();
        $this->info("📊 Всего в очереди ожидания: {$pending}");

        Log::info("📥 Сбор URL завершен: в очереди {$pending}");

        return self::SUCCESS;
    }
}
