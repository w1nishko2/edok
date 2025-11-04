<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class PublishRecipeCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:publish-collection 
                            {--category= : Название категории для подборки (если не указано - случайные рецепты)}
                            {--random-category : Выбрать случайную категорию}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Публикация подборки из 5 случайных рецептов в Telegram канал';

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
        $categoryName = null;

        // Если указана опция случайной категории
        if ($this->option('random-category')) {
            $category = Category::has('recipes', '>=', 5)->inRandomOrder()->first();
            
            if ($category) {
                $categoryName = $category->name;
                $recipesCount = $category->recipes()->count();
                $this->info("🎲 Выбрана случайная категория: {$categoryName} ({$recipesCount} рецептов)");
            } else {
                $this->warn('⚠️ Не найдено категорий с 5+ рецептами, публикуем смешанную подборку');
            }
        } 
        // Если указана конкретная категория
        elseif ($this->option('category')) {
            $categoryName = $this->option('category');
            
            // Проверяем, есть ли достаточно рецептов в категории
            $recipesInCategory = \App\Models\Recipe::whereHas('categories', function($q) use ($categoryName) {
                $q->where('name', 'LIKE', "%{$categoryName}%");
            })->count();
            
            if ($recipesInCategory >= 5) {
                $this->info("📂 Публикация подборки по категории: {$categoryName} ({$recipesInCategory} рецептов)");
            } else {
                $this->warn("⚠️ В категории '{$categoryName}' только {$recipesInCategory} рецептов (нужно минимум 5)");
                $this->info("🔄 Переключаемся на смешанную подборку");
                // categoryName останется, но сервис автоматически переключится на mixed
            }
        } 
        // Иначе смешанная подборка
        else {
            $totalRecipes = \App\Models\Recipe::count();
            $this->info("🎯 Публикация смешанной подборки (топ-5 рецептов дня) из {$totalRecipes} доступных");
        }

        try {
            $result = $this->telegramService->publishRecipeCollection($categoryName);

            if ($result) {
                $this->info("✅ Подборка рецептов успешно опубликована в Telegram!");
                
                if ($categoryName) {
                    $this->info("📚 Категория: {$categoryName}");
                } else {
                    $this->info("📚 Тип: Смешанная подборка");
                }
                
                $this->info("📢 Пост совместим с Яндекс.Дзеном");
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Не удалось опубликовать подборку рецептов');
                $this->error('Проверьте логи: storage/logs/laravel.log');
                
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Ошибка при публикации подборки: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
