<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'timeout' => 30,
    'verify' => false,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]
]);

$url = 'https://food.ru/recipes/263813-salat-osennii-den';
echo "Загружаю: {$url}\n";

$response = $client->get($url);
$html = $response->getBody()->getContents();

// Сохраняем полный HTML
file_put_contents(__DIR__ . '/recipe-page.html', $html);
echo "Сохранено в: recipe-page.html (" . strlen($html) . " байт)\n";

// Анализируем структуру
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

echo "\n=== АНАЛИЗ СТРУКТУРЫ ===\n\n";

// Название
$nodes = $xpath->query('//h1');
if ($nodes && $nodes->length > 0) {
    echo "✅ Название: " . trim($nodes->item(0)->textContent) . "\n";
}

// Описание
$nodes = $xpath->query('//meta[@name="description"]/@content');
if ($nodes && $nodes->length > 0) {
    echo "✅ Описание: " . substr(trim($nodes->item(0)->nodeValue), 0, 100) . "...\n";
}

// Время
$nodes = $xpath->query('//meta[@itemprop="totalTime"]/@content');
if ($nodes && $nodes->length > 0) {
    echo "✅ Время: " . $nodes->item(0)->nodeValue . "\n";
} else {
    // Альтернативный поиск
    $nodes = $xpath->query('//*[contains(text(), "мин")]');
    if ($nodes && $nodes->length > 0) {
        echo "⚠️ Время (альт): " . trim($nodes->item(0)->textContent) . "\n";
    }
}

// Порции
$nodes = $xpath->query('//span[contains(text(), "порци")]');
if ($nodes && $nodes->length > 0) {
    echo "✅ Порции: " . trim($nodes->item(0)->textContent) . "\n";
}

// Ингредиенты
$nodes = $xpath->query('//li[contains(@class, "ingredient")]');
if ($nodes && $nodes->length > 0) {
    echo "✅ Ингредиентов: " . $nodes->length . "\n";
    echo "   Пример: " . trim($nodes->item(0)->textContent) . "\n";
} else {
    $nodes = $xpath->query('//*[contains(text(), "Ингредиент")]/..//li | //*[contains(text(), "Ингредиент")]/../following-sibling::*//*[contains(@class, "ingredient")]');
    echo "⚠️ Ингредиентов (альт): " . ($nodes ? $nodes->length : 0) . "\n";
}

// Шаги
$nodes = $xpath->query('//ol//li | //div[contains(@class, "step")]');
if ($nodes && $nodes->length > 0) {
    echo "✅ Шагов приготовления: " . $nodes->length . "\n";
    echo "   Шаг 1: " . substr(trim($nodes->item(0)->textContent), 0, 80) . "...\n";
}

// Калории
$nodes = $xpath->query('//*[contains(text(), "ккал")]');
if ($nodes && $nodes->length > 0) {
    echo "✅ Калории найдены: " . trim($nodes->item(0)->textContent) . "\n";
}

// Изображение
$nodes = $xpath->query('//meta[@property="og:image"]/@content');
if ($nodes && $nodes->length > 0) {
    echo "✅ Изображение: " . substr($nodes->item(0)->nodeValue, 0, 80) . "...\n";
}

echo "\n✅ Готово! Проверьте файл recipe-page.html\n";
