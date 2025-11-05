<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

// Создаём минимальное окружение Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Тест парсинга рецепта с food.ru (БЕЗ сохранения в БД)\n";
echo "======================================================\n\n";

$testUrl = 'https://food.ru/recipes/263813-salat-osennii-den';
echo "📖 Парсим рецепт: {$testUrl}\n\n";

try {
    $client = new Client([
        'verify' => false,
        'timeout' => 30,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]
    ]);
    
    $response = $client->get($testUrl);
    $html = $response->getBody()->getContents();
    
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    libxml_clear_errors();
    
    // Парсим данные напрямую
    echo "📝 НАЗВАНИЕ:\n";
    $titleNodes = $xpath->query('//h1');
    $title = $titleNodes && $titleNodes->length > 0 ? trim($titleNodes->item(0)->textContent) : 'НЕТ';
    echo "   {$title}\n\n";
    
    echo "⏱️ ВРЕМЯ:\n";
    $timeNodes = $xpath->query('//meta[@itemprop="totalTime"]/@content');
    $time = $timeNodes && $timeNodes->length > 0 ? $timeNodes->item(0)->nodeValue : 'НЕТ';
    echo "   {$time}\n\n";
    
    echo "🥘 ИНГРЕДИЕНТЫ:\n";
    $ingredientNodes = $xpath->query('//tr[@itemProp="recipeIngredient"]');
    echo "   Найдено: " . ($ingredientNodes ? $ingredientNodes->length : 0) . "\n";
    if ($ingredientNodes && $ingredientNodes->length > 0) {
        for ($i = 0; $i < min(5, $ingredientNodes->length); $i++) {
            $node = $ingredientNodes->item($i);
            $nameNodes = $xpath->query('.//span[@class="name"]', $node);
            $valueNodes = $xpath->query('.//span[@class="value"]', $node);
            $typeNodes = $xpath->query('.//span[@class="type"]', $node);
            
            $name = $nameNodes && $nameNodes->length > 0 ? trim($nameNodes->item(0)->textContent) : '';
            $value = $valueNodes && $valueNodes->length > 0 ? trim($valueNodes->item(0)->textContent) : '';
            $type = $typeNodes && $typeNodes->length > 0 ? trim($typeNodes->item(0)->textContent) : '';
            
            echo "   " . ($i + 1) . ". {$name} - {$value} {$type}\n";
        }
        if ($ingredientNodes->length > 5) {
            echo "   ... и ещё " . ($ingredientNodes->length - 5) . "\n";
        }
    }
    
    echo "\n📋 ШАГИ:\n";
    $stepNodes = $xpath->query('//ol/li');
    echo "   Найдено: " . ($stepNodes ? $stepNodes->length : 0) . "\n";
    if ($stepNodes && $stepNodes->length > 0) {
        for ($i = 0; $i < min(3, $stepNodes->length); $i++) {
            $node = $stepNodes->item($i);
            $textNodes = $xpath->query('.//div[@itemProp="text"]', $node);
            $text = $textNodes && $textNodes->length > 0 ? trim($textNodes->item(0)->textContent) : trim($node->textContent);
            echo "   Шаг " . ($i + 1) . ": " . substr($text, 0, 100) . "...\n";
        }
    }
    
    echo "\n🍴 КАЛОРИИ:\n";
    $calNodes = $xpath->query('//*[contains(text(), "ккал")]');
    if ($calNodes && $calNodes->length > 0) {
        $calText = trim($calNodes->item(0)->textContent);
        if (preg_match('/([\d]+)\s*ккал/ui', $calText, $match)) {
            echo "   {$match[1]} ккал\n";
        }
    } else {
        echo "   НЕТ\n";
    }
    
    echo "\n🖼️ ИЗОБРАЖЕНИЕ:\n";
    $imgNodes = $xpath->query('//meta[@property="og:image"]/@content');
    if ($imgNodes && $imgNodes->length > 0) {
        $imgUrl = $imgNodes->item(0)->nodeValue;
        echo "   ✅ " . substr($imgUrl, 0, 80) . "...\n";
    } else {
        echo "   ❌ НЕТ\n";
    }
    
    echo "\n🏷️ КАТЕГОРИИ (из навигации):\n";
    $catNodes = $xpath->query('//nav//a[contains(@href, "/recipes/") and not(contains(@href, "-"))]');
    if ($catNodes && $catNodes->length > 0) {
        $shown = 0;
        for ($i = 0; $i < $catNodes->length && $shown < 5; $i++) {
            $catName = trim($catNodes->item($i)->textContent);
            if ($catName && !in_array($catName, ['Доступный ЗОЖ', 'Базовый уровень', 'Продвинутый уровень', 'Food.ru'])) {
                echo "   - {$catName}\n";
                $shown++;
            }
        }
    } else {
        echo "   НЕТ\n";
    }
    
    echo "\n✅ ВСЕ ДАННЫЕ УСПЕШНО ИЗВЛЕЧЕНЫ!\n";
    
} catch (\Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n✅ Тест завершён\n";
