<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\RecipeParserService;

// Создаём минимальное окружение Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Тест парсинга конкретного рецепта с food.ru\n";
echo "==============================================\n\n";

$parser = new RecipeParserService();

// Тестируем на первом рецепте из списка
$testUrl = 'https://food.ru/recipes/263813-salat-osennii-den';
echo "📖 Парсим рецепт: {$testUrl}\n\n";

try {
    $recipe = $parser->parseRecipe($testUrl);
    
    if ($recipe) {
        echo "✅ Рецепт успешно спарсен!\n\n";
        
        echo "📝 Основная информация:\n";
        echo "   Название: " . ($recipe->title ?? 'НЕТ') . "\n";
        echo "   Slug: " . ($recipe->slug ?? 'НЕТ') . "\n";
        echo "   Описание: " . (isset($recipe->description) ? mb_substr($recipe->description, 0, 100) . '...' : 'НЕТ') . "\n";
        echo "   Время приготовления: " . ($recipe->total_time ?? 'НЕТ') . " мин\n";
        echo "   Порций: " . ($recipe->servings ?? 'НЕТ') . "\n";
        echo "   Рейтинг: " . ($recipe->rating ?? 'НЕТ') . "\n\n";
        
        echo "🥘 Ингредиенты (" . count($recipe->ingredients ?? []) . "):\n";
        foreach (array_slice($recipe->ingredients ?? [], 0, 10) as $i => $ingredient) {
            echo "   " . ($i + 1) . ". " . $ingredient['name'] . " - " . ($ingredient['quantity'] ?: 'по вкусу') . "\n";
        }
        if (count($recipe->ingredients ?? []) > 10) {
            echo "   ... и ещё " . (count($recipe->ingredients) - 10) . "\n";
        }
        echo "\n";
        
        echo "📋 Шаги приготовления (" . count($recipe->steps ?? []) . "):\n";
        foreach (array_slice($recipe->steps ?? [], 0, 5) as $step) {
            echo "   Шаг " . $step['step_number'] . ": " . mb_substr($step['description'], 0, 100) . "...\n";
        }
        if (count($recipe->steps ?? []) > 5) {
            echo "   ... и ещё " . (count($recipe->steps) - 5) . " шагов\n";
        }
        echo "\n";
        
        echo "🍴 Питательность:\n";
        echo "   Калории: " . ($recipe->nutrition['calories'] ?? '0') . " ккал\n";
        echo "   Белки: " . ($recipe->nutrition['proteins'] ?? '0') . " г\n";
        echo "   Жиры: " . ($recipe->nutrition['fats'] ?? '0') . " г\n";
        echo "   Углеводы: " . ($recipe->nutrition['carbs'] ?? '0') . " г\n\n";
        
        echo "🖼️ Изображение: " . ($recipe->image_path ? 'ДА (' . $recipe->image_path . ')' : 'НЕТ') . "\n\n";
        
        echo "🏷️ Категории (" . count($recipe->categories ?? []) . "): ";
        if (count($recipe->categories ?? []) > 0) {
            echo implode(', ', array_column($recipe->categories, 'name')) . "\n\n";
        } else {
            echo "НЕТ\n\n";
        }
        
    } else {
        echo "❌ Не удалось спарсить рецепт (вернулся null)\n";
        echo "💡 Возможно, рецепт уже существует в базе данных\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Стек: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Тест RecipeParserService завершён\n";
