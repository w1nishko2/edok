<?php

namespace App\Console\Commands;

use App\Models\RecipeQueue;
use App\Services\RecipeListParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CollectRecipeUrls extends Command
{
    protected $signature = 'recipes:collect-urls 
                            {--count=100 : Количество URL для сбора}';

    protected $description = 'Сбор URL рецептов и добавление в очередь (легкая задача, каждые 15 мин)';

    public function handle(RecipeListParserService $parser): int
    {
        $targetCount = (int) $this->option('count');

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   📥 Сбор URL рецептов в очередь                     ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->info("🎯 Цель: {$targetCount} новых URL");
        $this->newLine();

        // Собираем URL
        $urls = $parser->parseMultiplePages($targetCount);

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
