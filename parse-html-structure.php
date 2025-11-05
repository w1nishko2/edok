<?php

$html = file_get_contents(__DIR__ . '/recipe-page.html');

echo "=== ПОИСК СЕЛЕКТОРОВ В HTML ===\n\n";

// Ищем ингредиенты
echo "🔍 ИНГРЕДИЕНТЫ:\n";
if (preg_match_all('/<li[^>]*ingredient[^>]*>(.*?)<\/li>/is', $html, $matches)) {
    echo "✅ Найдено <li> с 'ingredient': " . count($matches[0]) . "\n";
    echo "Пример 1: " . substr(strip_tags($matches[1][0]), 0, 100) . "\n";
    if (isset($matches[1][1])) {
        echo "Пример 2: " . substr(strip_tags($matches[1][1]), 0, 100) . "\n";
    }
} else {
    echo "❌ <li> с 'ingredient' не найдены\n";
}

// Ищем класс ингредиентов
if (preg_match('/class="([^"]*ingredient[^"]*)"/', $html, $match)) {
    echo "Класс: {$match[1]}\n";
}

echo "\n🔍 АЛЬТЕРНАТИВНЫЙ ПОИСК ИНГРЕДИЕНТОВ:\n";
// Ищем заголовок "Ингредиенты"
if (preg_match('/(Ингредиент[^<]*)/u', $html, $match)) {
    echo "✅ Найден заголовок: {$match[1]}\n";
    
    // Ищем что идёт после заголовка
    $pos = strpos($html, $match[0]);
    $after = substr($html, $pos, 2000);
    
    if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $after, $matches)) {
        echo "После заголовка найдено <li>: " . count($matches[0]) . "\n";
        echo "Пример: " . substr(strip_tags($matches[1][0]), 0, 150) . "\n";
    }
}

echo "\n🔍 ПОРЦИИ:\n";
if (preg_match('/([\d]+)\s*порци/ui', $html, $match)) {
    echo "✅ Порции: {$match[1]}\n";
} else {
    echo "❌ Порции не найдены\n";
}

echo "\n🔍 КАЛОРИИ И БЖУ:\n";
if (preg_match('/([\d]+)\s*ккал/ui', $html, $match)) {
    echo "✅ Калории: {$match[1]} ккал\n";
}
if (preg_match('/белк[а-я]*[:\s]*([\d\.]+)/ui', $html, $match)) {
    echo "✅ Белки: {$match[1]} г\n";
}
if (preg_match('/жир[а-я]*[:\s]*([\d\.]+)/ui', $html, $match)) {
    echo "✅ Жиры: {$match[1]} г\n";
}
if (preg_match('/углевод[а-я]*[:\s]*([\d\.]+)/ui', $html, $match)) {
    echo "✅ Углеводы: {$match[1]} г\n";
}

echo "\n🔍 РЕЙТИНГ:\n";
if (preg_match('/ratingValue["\s:]*([0-9\.]+)/i', $html, $match)) {
    echo "✅ Рейтинг: {$match[1]}\n";
}
if (preg_match('/ratingCount["\s:]*([0-9]+)/i', $html, $match)) {
    echo "✅ Оценок: {$match[1]}\n";
}

echo "\n🔍 ШАГИ ПРИГОТОВЛЕНИЯ:\n";
// Ищем пронумерованные шаги
if (preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $html, $matches)) {
    echo "✅ Найдено <ol>: " . count($matches[0]) . "\n";
    
    // Считаем <li> внутри <ol>
    if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $matches[1][0], $steps)) {
        echo "Шагов в первом <ol>: " . count($steps[0]) . "\n";
        echo "Шаг 1: " . substr(strip_tags($steps[1][0]), 0, 100) . "...\n";
    }
}

echo "\n✅ Анализ завершён\n";
