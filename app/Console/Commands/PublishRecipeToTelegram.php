<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Models\TelegramPost;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class PublishRecipeToTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:publish-recipe 
                            {--recipe-id= : ID конкретного рецепта для публикации}
                            {--with-button : Добавить кнопку "Смотреть рецепт" (может не синхронизироваться с Дзеном)}
                            {--test : Тестовый режим - только проверка соединения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Публикация рецепта в Telegram канал';

    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Тестовый режим
        if ($this->option('test')) {
            $this->info('🔍 Проверка соединения с Telegram Bot...');
            
            if ($this->telegramService->testConnection()) {
                $this->info('✅ Соединение успешно установлено!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Не удалось подключиться к Telegram Bot');
                return Command::FAILURE;
            }
        }

        // Публикация конкретного рецепта
        if ($recipeId = $this->option('recipe-id')) {
            return $this->publishSpecificRecipe($recipeId);
        }

        // Публикация следующего неопубликованного рецепта
        return $this->publishNextRecipe();
    }

    /**
     * Публикация конкретного рецепта по ID
     */
    protected function publishSpecificRecipe(int $recipeId): int
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            $this->error("❌ Рецепт с ID {$recipeId} не найден");
            return Command::FAILURE;
        }

        $this->info("📝 Публикация рецепта: {$recipe->title}");

        return $this->publishRecipe($recipe);
    }

    /**
     * Публикация следующего неопубликованного рецепта
     */
    protected function publishNextRecipe(): int
    {
        // Находим рецепт, который еще не публиковался
        $recipe = Recipe::whereDoesntHave('telegramPosts', function ($query) {
            $query->where('status', 'success');
        })
        ->orderBy('created_at', 'desc') // Сначала новые рецепты
        ->first();

        if (!$recipe) {
            $this->warn('⚠️ Нет неопубликованных рецептов');
            
            // Альтернатива: публикуем случайный старый рецепт
            $recipe = Recipe::inRandomOrder()->first();
            
            if (!$recipe) {
                $this->error('❌ В базе данных нет рецептов');
                return Command::FAILURE;
            }
            
            $this->info('🎲 Публикуем случайный рецепт для поддержания активности канала');
        }

        $this->info("📝 Публикация рецепта: {$recipe->title}");

        return $this->publishRecipe($recipe);
    }

    /**
     * Публикация рецепта
     */
    protected function publishRecipe(Recipe $recipe): int
    {
        // Создаем запись о попытке публикации
        $telegramPost = TelegramPost::create([
            'recipe_id' => $recipe->id,
            'channel_id' => config('services.telegram.channel_id'),
            'status' => 'pending',
        ]);

        try {
            // Определяем, нужна ли кнопка
            $withButton = $this->option('with-button');
            
            if (!$withButton) {
                $this->info('ℹ️ Публикация БЕЗ кнопки (совместимо с Яндекс.Дзеном)');
            } else {
                $this->warn('⚠️ Публикация С кнопкой (может не синхронизироваться с Дзеном)');
            }

            // Публикуем рецепт
            $result = $this->telegramService->publishRecipe($recipe, $withButton);

            if ($result) {
                // Отмечаем как успешно опубликованный
                $telegramPost->markAsPublished('success');
                
                $this->info("✅ Рецепт успешно опубликован в Telegram!");
                $this->info("🔗 Ссылка на рецепт: " . route('recipe.show', $recipe->slug));
                
                if (!$withButton) {
                    $this->info("📢 Пост совместим с Яндекс.Дзеном - будет автоматически опубликован в канале Дзена");
                }
                
                return Command::SUCCESS;
            } else {
                $telegramPost->markAsFailed('Неизвестная ошибка при публикации');
                $this->error('❌ Не удалось опубликовать рецепт');
                
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $telegramPost->markAsFailed($e->getMessage());
            
            $this->error('❌ Ошибка при публикации: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
