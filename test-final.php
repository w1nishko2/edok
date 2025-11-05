<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\RecipeParserService;

// Создаём минимальное окружение Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Финальный тест парсера RecipeParserService\n";
echo "=============================================\n\n";

$parser = new RecipeParserService();
$url = 'https://food.ru/recipes/263813-salat-osennii-den';

echo "📖 URL: {$url}\n\n";

try {
    // Используем рефлексию чтобы вызвать защищённые методы
    $reflection = new ReflectionClass($parser);
    
    // Загружаем HTML
    $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
    $response = $client->get($url);
    $html = $response->getBody()->getContents();
    
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    // Тестируем parseIngredients
    $method = $reflection->getMethod('parseIngredients');
    $method->setAccessible(true);
    $ingredients = $method->invoke($parser, $xpath);
    
    echo "🥘 ИНГРЕДИЕНТЫ: " . count($ingredients) . "\n";
    foreach (array_slice($ingredients, 0, 5) as $i => $ing) {
        echo "   " . ($i+1) . ". {$ing['name']} - {$ing['quantity']}\n";
    }
    
    // Тестируем parseSteps
    $method = $reflection->getMethod('parseSteps');
    $method->setAccessible(true);
    $steps = $method->invoke($parser, $xpath);
    
    echo "\n📋 ШАГИ: " . count($steps) . "\n";
    foreach (array_slice($steps, 0, 3) as $step) {
        echo "   Шаг {$step['step_number']}: " . substr($step['description'], 0, 70) . "...\n";
    }
    
    // Тестируем parseNutrition
    $method = $reflection->getMethod('parseNutrition');
    $method->setAccessible(true);
    $nutrition = $method->invoke($parser, $xpath);
    
    echo "\n🍴 ПИТАТЕЛЬНОСТЬ:\n";
    echo "   Калории: {$nutrition['calories']} ккал\n";
    echo "   Белки: {$nutrition['proteins']} г\n";
    echo "   Жиры: {$nutrition['fats']} г\n";
    echo "   Углеводы: {$nutrition['carbs']} г\n";
    
    echo "\n";
    if (count($ingredients) > 0 && count($steps) > 0) {
        echo "✅ ВСЁ РАБОТАЕТ ОТЛИЧНО!\n";
    } else {
        echo "❌ Проблемы с парсингом\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n✅ Тест завершён\n";
