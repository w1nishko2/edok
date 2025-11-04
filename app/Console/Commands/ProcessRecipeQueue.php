<?php

namespace App\Console\Commands;

use App\Models\RecipeQueue;
use App\Services\RecipeParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecipeQueue extends Command
{
    protected $signature = 'recipes:process-queue 
                            {--limit=50 : Количество задач для обработки за раз}';

    protected $description = 'Обработка очереди рецептов - парсинг полных данных (тяжелая задача, каждые 30 мин)';

    protected RecipeParserService $parser;

    public function __construct(RecipeParserService $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   ⚙️  Обработка очереди рецептов                      ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        // Получаем задачи на обработку
        $tasks = RecipeQueue::getPendingTasks($limit);

        if ($tasks->isEmpty()) {
            $this->warn("⚠️ Очередь пуста - нечего обрабатывать");
            Log::info("⚠️ Очередь обработки пуста");
            return self::SUCCESS;
        }

        $this->info("📊 Найдено задач: " . $tasks->count());
        $this->info("🎯 Будет обработано: {$limit}");
        $this->newLine();

        $success = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($tasks->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - Успешно: %message%');
        $progressBar->setMessage('0');

        foreach ($tasks as $task) {
            try {
                // Отмечаем как обрабатываемую
                $task->markAsProcessing();

                // Парсим рецепт
                $recipe = $this->parser->parseRecipe($task->url);

                if ($recipe) {
                    // Успех
                    $task->markAsCompleted();
                    $success++;
                    $progressBar->setMessage((string) $success);

                    Log::info("✅ Рецепт обработан: {$task->url}");
                } else {
                    // Ошибка парсинга
                    $task->markAsFailed('Не удалось спарсить рецепт');
                    $failed++;

                    Log::warning("❌ Не удалось спарсить: {$task->url}");
                }

                $progressBar->advance();
                sleep(2); // Пауза между запросами (важно для хостинга!)

            } catch (\Exception $e) {
                $task->markAsFailed($e->getMessage());
                $failed++;
                $progressBar->advance();

                Log::error("❌ Ошибка обработки {$task->url}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║   ✅ Обработка завершена                              ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->info("✅ Успешно обработано: {$success}");
        $this->info("❌ Ошибок: {$failed}");

        // Статистика очереди
        $pending = RecipeQueue::where('status', RecipeQueue::STATUS_PENDING)->count();
        $completed = RecipeQueue::where('status', RecipeQueue::STATUS_COMPLETED)->count();
        $failedTotal = RecipeQueue::where('status', RecipeQueue::STATUS_FAILED)->count();

        $this->newLine();
        $this->info("📊 Общая статистика очереди:");
        $this->info("   ⏳ Ожидают: {$pending}");
        $this->info("   ✅ Выполнено: {$completed}");
        $this->info("   ❌ Провалено: {$failedTotal}");

        Log::info("⚙️ Обработка очереди завершена: успешно {$success}, ошибок {$failed}, осталось {$pending}");

        return self::SUCCESS;
    }
}
