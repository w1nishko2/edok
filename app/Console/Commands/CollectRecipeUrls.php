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
        $category = $this->argument('category');
        $targetCount = (int) $this->option('count');
        $startPage = (int) $this->option('start-page');

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   📥 Сбор URL рецептов с Povar.ru                    ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        // Если категория не указана, собираем из всех
        if (!$category) {
            $categories = $parser->getCategories();
            $this->info("� Будут обработаны все категории:");
            foreach ($categories as $slug => $name) {
                $this->info("  • {$slug} - {$name}");
            }
            $this->newLine();
            
            $allUrls = [];
            foreach (array_keys($categories) as $slug) {
                $this->info("🔍 Обрабатываем категорию: {$slug}");
                $urls = $parser->parseMultiplePages($slug, $targetCount, $startPage);
                $allUrls = array_merge($allUrls, $urls);
                
                if (count($urls) > 0) {
                    $this->info("  ✅ Найдено: " . count($urls));
                }
                
                sleep(rand(2, 4)); // Пауза между категориями
            }
            
            $urls = $allUrls;
        } else {
            $this->info("🎯 Категория: {$category}");
            $this->info("🎯 Цель: {$targetCount} новых URL");
            $this->info("📄 Начальная страница: {$startPage}");
            $this->newLine();

            $urls = $parser->parseMultiplePages($category, $targetCount, $startPage);
        }

        if (empty($urls)) {
            $this->warn("⚠️ Не найдено новых рецептов для добавления в очередь");
            return self::SUCCESS;
        }

        $this->info("✅ Собрано " . count($urls) . " новых URL");
        $this->newLine();

        // Добавляем в очередь
        $added = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar(count($urls));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - Добавлено: %message%');
        $progressBar->setMessage('0');

        foreach ($urls as $url) {
            try {
                // Проверяем, нет ли уже в очереди
                $exists = RecipeQueue::where('url', $url)->exists();
                
                if (!$exists) {
                    RecipeQueue::create([
                        'url' => $url,
                        'status' => RecipeQueue::STATUS_PENDING,
                    ]);
                    $added++;
                    $progressBar->setMessage((string) $added);
                } else {
                    $skipped++;
                }

                $progressBar->advance();

            } catch (\Exception $e) {
                Log::error("❌ Ошибка добавления URL в очередь: {$url}", [
                    'error' => $e->getMessage()
                ]);
                $skipped++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   ✅ Сбор URL завершен                                ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->info("✅ Добавлено в очередь: {$added}");
        $this->info("⏭️  Пропущено (дубликаты): {$skipped}");
        
        // Статистика очереди
        $pending = RecipeQueue::where('status', RecipeQueue::STATUS_PENDING)->count();
        $this->info("📊 Всего в очереди ожидания: {$pending}");

        Log::info("📥 Сбор URL завершен: добавлено {$added}, пропущено {$skipped}, в очереди {$pending}");

        return self::SUCCESS;
    }
}
