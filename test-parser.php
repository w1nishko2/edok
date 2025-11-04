<?php
require __DIR__ . '/vendor/autoload.php';
use Illuminate\Foundation\Application;
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$parser = app(\App\Services\RecipeParserService::class);
$queue = \App\Models\RecipeQueue::where('status', \App\Models\RecipeQueue::STATUS_PENDING)
    ->first();
if (!$queue) {
    echo "❌ Нет задач в очереди для тестирования\n";
    echo "💡 Создаю тестовую задачу...\n";
    $queue = \App\Models\RecipeQueue::create([
        'url' => 'https://povar.ru/recipes/salat_mimoza_klassicheskii-601.html',
        'status' => \App\Models\RecipeQueue::STATUS_PENDING,
    ]);
    echo "✅ Тестовая задача создана\n\n";
}
$oldRecipe = \App\Models\Recipe::where('source_url', $queue->url)->first();
if ($oldRecipe) {
    echo "🗑️ Удаляю старый рецепт для повторного теста...\n";
    $oldRecipe->categories()->detach();
    $oldRecipe->delete();
    echo "✅ Старый рецепт удален\n\n";
}
echo "🔍 Тестирование парсера для URL: {$queue->url}\n\n";
try {
    $recipe = $parser->parseRecipe($queue->url);
    if ($recipe) {
        echo "✅ Рецепт успешно спарсен и сохранен!\n\n";
        echo "📋 Информация о рецепте:\n";
        echo "   ID: {$recipe->id}\n";
        echo "   Название: {$recipe->title}\n";
        echo "   Slug: {$recipe->slug}\n";
        echo "   Время: {$recipe->total_time} мин\n";
        echo "   Порций: {$recipe->servings}\n";
        echo "   Рейтинг: {$recipe->rating} ({$recipe->rating_count} оценок)\n";
        echo "   Изображение: " . ($recipe->image_path ? '✅' : '❌') . "\n";
        echo "   Ингредиентов: " . count($recipe->ingredients ?? []) . "\n";
        echo "   Шагов: " . count($recipe->steps ?? []) . "\n";
        if (!empty($recipe->ingredients)) {
            echo "\n🥗 Первые 3 ингредиента:\n";
            foreach (array_slice($recipe->ingredients, 0, 3) as $ing) {
                echo "   - {$ing['name']}: {$ing['quantity']} {$ing['measure']}\n";
            }
        }
        if (!empty($recipe->steps)) {
            echo "\n📝 Первый шаг:\n";
            $firstStep = $recipe->steps[0];
            echo "   {$firstStep['step_number']}. " . mb_substr($firstStep['description'], 0, 100) . "...\n";
        }
        $categories = $recipe->categories;
        if ($categories->count() > 0) {
            echo "\n🏷️ Категории:\n";
            foreach ($categories as $cat) {
                echo "   - {$cat->name}\n";
            }
        }
        echo "\n✅ ВСЕ РАБОТАЕТ ОТЛИЧНО!\n";
        
    } else {
        echo "❌ Рецепт не был создан (возможно уже существует)\n";
    }
} catch (\Exception $e) {
    echo "❌ ОШИБКА: {$e->getMessage()}\n";
    echo "\n📋 Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}