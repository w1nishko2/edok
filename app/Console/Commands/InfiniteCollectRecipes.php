<?php

namespace App\Console\Commands;

use App\Models\RecipeQueue;
use App\Services\RecipeListParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InfiniteCollectRecipes extends Command
{
    protected $signature = 'recipes:collect-infinite 
                            {--delay=60 : Задержка между страницами в секундах (по умолчанию 1 минута)}';

    protected $description = 'Бесконечный сбор новых рецептов - по 1 странице каждую минуту из всех категорий';

    private array $categoryPages = [];
    private int $currentCategoryIndex = 0;

    public function handle(RecipeListParserService $parser): int
    {
        $delay = (int) $this->option('delay');
        
        $categories = array_keys($parser->getCategories());
        
        // Инициализируем страницы для каждой категории
        foreach ($categories as $category) {
            $this->categoryPages[$category] = 1;
        }

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   🔄 БЕСКОНЕЧНЫЙ СБОР РЕЦЕПТОВ                        ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();
        $this->info("⚙️  Категорий: " . count($categories));
        $this->info("⚙️  По 1 странице каждые {$delay} секунд");
        $this->info("⚙️  Для остановки нажмите Ctrl+C");
        $this->newLine();
        $this->info("📋 Категории: " . implode(', ', $categories));
        $this->newLine(2);

        Log::info("🚀 Запущен бесконечный сбор рецептов", [
            'categories' => count($categories),
            'delay_seconds' => $delay
        ]);

        $cycle = 0;
        
        while (true) {
            $cycle++;
            $startTime = microtime(true);
            
            // Выбираем текущую категорию по кругу
            $category = $categories[$this->currentCategoryIndex];
            $currentPage = $this->categoryPages[$category];
            
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🔄 Цикл #{$cycle} | 📂 {$category} | 📄 Стр.{$currentPage}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            
            try {
                // Парсим одну страницу
                $urls = $parser->parseRecipesList($category, $currentPage);
                
                if (empty($urls)) {
                    $this->warn("⚠️  Страница {$currentPage} в категории {$category} пуста");
                    $this->warn("🔄 Начинаем сначала с 1-й страницы...");
                    $this->categoryPages[$category] = 1;
                    
                    // Переходим к следующей категории
                    $this->currentCategoryIndex = ($this->currentCategoryIndex + 1) % count($categories);
                    
                    sleep(3);
                    continue;
                }

                $this->info("📥 Найдено URL: " . count($urls));

                // Фильтруем уже существующие
                $newUrls = [];
                foreach ($urls as $url) {
                    if (!RecipeQueue::where('url', $url)->exists()) {
                        $newUrls[] = $url;
                    }
                }

                if (empty($newUrls)) {
                    $this->info("⏭️  Все рецепты уже в базе, следующая страница");
                    $this->categoryPages[$category]++;
                } else {
                    $this->info("✨ Новых рецептов: " . count($newUrls));

                    // Добавляем в очередь
                    $added = 0;
                    foreach ($newUrls as $url) {
                        try {
                            RecipeQueue::create([
                                'url' => $url,
                                'status' => RecipeQueue::STATUS_PENDING,
                            ]);
                            $added++;
                        } catch (\Exception $e) {
                            Log::error("Ошибка добавления URL: {$url}", ['error' => $e->getMessage()]);
                        }
                    }

                    $this->info("✅ Добавлено: {$added}");
                    
                    // Переходим на следующую страницу
                    $this->categoryPages[$category]++;
                }

                // Статистика очереди
                $pending = RecipeQueue::where('status', RecipeQueue::STATUS_PENDING)->count();
                $completed = RecipeQueue::where('status', RecipeQueue::STATUS_COMPLETED)->count();
                
                $this->info("📊 Очередь: ⏳ {$pending} ожидают | ✅ {$completed} готово");

                // Переходим к следующей категории
                $this->currentCategoryIndex = ($this->currentCategoryIndex + 1) % count($categories);
                
                $elapsed = round(microtime(true) - $startTime, 2);
                $this->info("⏱️  {$elapsed} сек | ⏸️  Пауза {$delay} сек...");
                $this->newLine();
                
                sleep($delay);

            } catch (\Exception $e) {
                $this->error("❌ Ошибка: " . $e->getMessage());
                Log::error("Ошибка в бесконечном парсере", [
                    'cycle' => $cycle,
                    'category' => $category,
                    'page' => $currentPage,
                    'error' => $e->getMessage()
                ]);
                
                // При ошибке переходим к следующей категории
                $this->currentCategoryIndex = ($this->currentCategoryIndex + 1) % count($categories);
                
                sleep(10);
            }
        }

        return self::SUCCESS;
    }
}
