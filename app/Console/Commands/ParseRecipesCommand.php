<?php

namespace App\Console\Commands;

use App\Services\RecipeListParserService;
use App\Services\RecipeParserService;
use Illuminate\Console\Command;

class ParseRecipesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:parse 
                            {--count=30 : Точное количество НОВЫХ рецептов для парсинга}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг ТОЧНОГО количества новых рецептов с сайта 1000.menu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetCount = (int) $this->option('count');

        $this->info("🚀 Начинаем парсинг рецептов...");
        $this->info("🎯 Цель: найти и добавить РОВНО {$targetCount} НОВЫХ рецептов");
        $this->info("✅ Рецепты, которые уже есть в базе, будут автоматически пропущены");
        $this->newLine();

        $startTime = microtime(true);

        // Шаг 1: Получаем список URL НОВЫХ рецептов (точное количество)
        $listParser = new RecipeListParserService();
        $this->info("🔍 Ищем новые рецепты...");
        
        $recipeUrls = $listParser->parseMultiplePages($targetCount);
        
        $foundCount = count($recipeUrls);
        $this->info("✅ Найдено новых рецептов: {$foundCount}");
        
        if ($foundCount < $targetCount) {
            $this->warn("⚠️ Внимание: найдено только {$foundCount} из {$targetCount} запрошенных");
        }
        
        $this->newLine();

        if (empty($recipeUrls)) {
            $this->error("❌ Не найдено ни одного нового рецепта!");
            $this->info("💡 Возможно, все доступные рецепты уже в базе");
            return 1;
        }

        // Шаг 2: Парсим каждый рецепт
        $recipeParser = new RecipeParserService();
        
        $this->info("📝 Начинаем обработку {$foundCount} рецептов...");
        $this->newLine();
        
        $progressBar = $this->output->createProgressBar($foundCount);
        $progressBar->start();

        $successful = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($recipeUrls as $url) {
            try {
                $recipe = $recipeParser->parseRecipe($url);
                
                if ($recipe) {
                    $successful++;
                } else {
                    $skipped++;
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Ошибка при парсинге {$url}: " . $e->getMessage());
            }

            $progressBar->advance();
            
            // Задержка между запросами (1-3 секунды случайно)
            sleep(rand(1, 3));
        }

        $progressBar->finish();
        $this->newLine(2);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Итоги
        $this->info("✨ Парсинг завершен!");
        $this->info("⏱️ Время выполнения: {$duration} сек");
        $this->newLine();
        
        $this->table(
            ['Статус', 'Количество'],
            [
                ['✅ Успешно добавлено', $successful],
                ['⏭️ Пропущено (дубликаты/ошибки)', $skipped],
                ['❌ Ошибки парсинга', $errors],
                ['📊 Всего обработано URL', $foundCount],
                ['🎯 Целевое количество', $targetCount],
            ]
        );

        if ($successful >= $targetCount * 0.9) {
            $this->info("🎉 Отлично! Собрано {$successful} рецептов");
        } elseif ($successful > 0) {
            $this->warn("⚠️ Собрано меньше запрошенного: {$successful}/{$targetCount}");
        } else {
            $this->error("❌ Не удалось добавить ни одного рецепта");
        }

        return $successful > 0 ? 0 : 1;
    }
}
