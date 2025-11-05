<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\WatermarkService;

// Создаём минимальное окружение Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎨 Тест добавления водяных знаков\n";
echo "=================================\n\n";

// Проверяем настройки
echo "📋 Настройки:\n";
echo "   WATERMARK_ENABLED: " . (config('app.watermark_enabled') ? 'ДА' : 'НЕТ') . "\n";
echo "   WATERMARK_TEXT: " . config('app.watermark_text') . "\n\n";

// Проверяем расширение GD
if (!extension_loaded('gd')) {
    echo "❌ Расширение GD не установлено!\n";
    echo "   Установите расширение GD для PHP\n";
    exit(1);
}

echo "✅ Расширение GD установлено\n";
echo "   Версия GD: " . GD_VERSION . "\n\n";

// Проверяем поддержку форматов
$formats = [];
if (function_exists('imagecreatefromjpeg')) $formats[] = 'JPEG';
if (function_exists('imagecreatefrompng')) $formats[] = 'PNG';
if (function_exists('imagecreatefromgif')) $formats[] = 'GIF';
if (function_exists('imagecreatefromwebp')) $formats[] = 'WebP';

echo "📷 Поддерживаемые форматы: " . implode(', ', $formats) . "\n\n";

// Проверяем наличие шрифтов
$watermarkService = new WatermarkService();
$reflection = new ReflectionClass($watermarkService);
$method = $reflection->getMethod('findSystemFont');
$method->setAccessible(true);
$font = $method->invoke($watermarkService);

if ($font) {
    echo "✅ Системный шрифт найден: {$font}\n\n";
} else {
    echo "⚠️ Системный шрифт не найден, будет использован встроенный шрифт GD\n\n";
}

// Тестируем на существующем изображении (если есть)
echo "🔍 Поиск изображений для теста...\n";
$storageDir = storage_path('app/public/recipes');

if (!is_dir($storageDir)) {
    echo "⚠️ Директория {$storageDir} не существует\n";
    echo "   Создайте директорию или загрузите первый рецепт\n";
} else {
    $images = glob($storageDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    if (empty($images)) {
        echo "⚠️ Изображения не найдены в {$storageDir}\n";
        echo "   Запустите парсер для загрузки изображений\n";
    } else {
        echo "✅ Найдено изображений: " . count($images) . "\n";
        $testImage = basename($images[0]);
        echo "   Тестовое изображение: {$testImage}\n\n";
        
        // Создаём копию для теста
        $originalPath = 'recipes/' . $testImage;
        $testPath = 'recipes/test_watermark_' . $testImage;
        
        if (copy(
            storage_path('app/public/' . $originalPath),
            storage_path('app/public/' . $testPath)
        )) {
            echo "✅ Создана копия для теста: {$testPath}\n";
            echo "🎨 Добавляем водяной знак...\n";
            
            $result = $watermarkService->addWatermark($testPath);
            
            if ($result) {
                echo "✅ УСПЕШНО! Водяной знак добавлен\n";
                echo "   Проверьте файл: storage/app/public/{$testPath}\n";
            } else {
                echo "❌ Ошибка добавления водяного знака\n";
            }
        } else {
            echo "❌ Не удалось создать копию файла для теста\n";
        }
    }
}

echo "\n✅ Тест завершён\n";
