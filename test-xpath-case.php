<?php

$html = file_get_contents(__DIR__ . '/recipe-page.html');

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);

echo "=== ТЕСТ XPATH ЗАПРОСОВ ===\n\n";

echo "ИНГРЕДИЕНТЫ:\n";
$queries = [
    '//tr[@itemProp="recipeIngredient"]' => 'itemProp с большой P',
    '//tr[@itemprop="recipeIngredient"]' => 'itemprop с маленькой p',
    '//tr[contains(@class, "ingredient")]' => 'class="ingredient"',
];

foreach ($queries as $query => $desc) {
    $nodes = $xpath->query($query);
    echo "{$desc}: " . ($nodes ? $nodes->length : 0) . "\n";
    if ($nodes && $nodes->length > 0) {
        $name = $xpath->query('.//span[@class="name"]', $nodes->item(0));
        if ($name && $name->length > 0) {
            echo "  Пример: " . trim($name->item(0)->textContent) . "\n";
        }
    }
}

echo "\nШАГИ:\n";
$queries = [
    '//*[@itemType="https://schema.org/HowToStep"]' => 'itemType с большой T',
    '//*[@itemtype="https://schema.org/HowToStep"]' => 'itemtype с маленькой t',
    '//div[contains(@class, "step")]' => 'div с классом step',
];

foreach ($queries as $query => $desc) {
    $nodes = $xpath->query($query);
    echo "{$desc}: " . ($nodes ? $nodes->length : 0) . "\n";
    if ($nodes && $nodes->length > 0) {
        $text = $xpath->query('.//*[@itemProp="text" or @itemprop="text"]', $nodes->item(0));
        if ($text && $text->length > 0) {
            echo "  Текст: " . substr(trim($text->item(0)->textContent), 0, 60) . "...\n";
        }
    }
}

echo "\n✅ Готово\n";
