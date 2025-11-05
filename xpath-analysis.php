<?php

$html = file_get_contents(__DIR__ . '/recipe-page.html');

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

echo "=== ДЕТАЛЬНЫЙ АНАЛИЗ С XPATH ===\n\n";

echo "🔍 ИНГРЕДИЕНТЫ:\n";
$selectors = [
    '//ul/li' => 'Все <li> внутри <ul>',
    '//li[contains(@class, "ingredient")]' => '<li> с классом ingredient',
    '//*[contains(text(), "Ингредиенты")]/../following-sibling::*/li' => 'После заголовка Ингредиенты',
    '//*[contains(text(), "Ингредиенты")]/following::ul[1]/li' => 'Первый <ul> после Ингредиенты',
    '//h2[contains(text(), "Ингредиенты")]/following-sibling::ul[1]/li' => '<ul> после <h2>Ингредиенты',
    '//div[contains(@class, "ingredients")]//li' => '<li> внутри div.ingredients',
];

foreach ($selectors as $sel => $desc) {
    $nodes = $xpath->query($sel);
    $count = $nodes ? $nodes->length : 0;
    echo "  {$desc}: {$count}\n";
    if ($count > 0 && $count < 100) {
        for ($i = 0; $i < min(3, $count); $i++) {
            $text = trim($nodes->item($i)->textContent);
            if (strlen($text) < 200 && strlen($text) > 0) {
                echo "    " . ($i+1) . ". " . substr($text, 0, 100) . "\n";
            }
        }
    }
}

echo "\n🔍 ПОРЦИИ:\n";
$selectors = [
    '//*[contains(text(), "порци")]' => 'Текст "порци"',
    '//*[contains(@class, "serving")]' => 'Класс serving',
    '//span[contains(text(), "порци")]' => '<span> с "порци"',
];
foreach ($selectors as $sel => $desc) {
    $nodes = $xpath->query($sel);
    if ($nodes && $nodes->length > 0) {
        echo "  ✅ {$desc}: " . trim($nodes->item(0)->textContent) . "\n";
    }
}

echo "\n🔍 ШАГИ:\n";
$selectors = [
    '//ol/li' => '<ol> > <li>',
    '//ol[1]/li' => 'Первый <ol>',
    '//*[contains(text(), "Приготовление")]/following::ol[1]/li' => 'После "Приготовление"',
];
foreach ($selectors as $sel => $desc) {
    $nodes = $xpath->query($sel);
    $count = $nodes ? $nodes->length : 0;
    echo "  {$desc}: {$count}\n";
    if ($count > 0 && $count < 20) {
        $text = trim($nodes->item(0)->textContent);
        echo "    Шаг 1: " . substr($text, 0, 100) . "...\n";
    }
}

echo "\n🔍 ИЗУЧАЕМ СТРУКТУРУ ВОКРУГ 'Ингредиенты':\n";
$nodes = $xpath->query('//*[contains(text(), "Ингредиенты")]');
if ($nodes && $nodes->length > 0) {
    echo "Найдено элементов с текстом 'Ингредиенты': " . $nodes->length . "\n";
    
    for ($i = 0; $i < min(3, $nodes->length); $i++) {
        $node = $nodes->item($i);
        echo "\nЭлемент {$i}:\n";
        echo "  Тег: " . $node->nodeName . "\n";
        echo "  Класс: " . ($node->hasAttribute('class') ? $node->getAttribute('class') : 'нет') . "\n";
        echo "  Текст: " . substr(trim($node->textContent), 0, 80) . "\n";
        
        // Проверяем следующие элементы
        $next = $node->parentNode;
        if ($next) {
            $nextUl = $xpath->query('.//ul', $next);
            if ($nextUl && $nextUl->length > 0) {
                $lis = $xpath->query('.//li', $nextUl->item(0));
                echo "  <ul> в родителе, <li>: " . ($lis ? $lis->length : 0) . "\n";
                if ($lis && $lis->length > 0) {
                    echo "    Пример: " . substr(trim($lis->item(0)->textContent), 0, 100) . "\n";
                }
            }
        }
    }
}

echo "\n✅ Готово!\n";
