<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WatermarkService
{
    /**
     * Добавляет текстовый водяной знак на изображение используя GD
     *
     * @param string $imagePath Путь к изображению относительно storage/app/public
     * @param string|null $watermarkText Текст водяного знака (по умолчанию из .env APP_NAME)
     * @return bool Успешность операции
     */
    public function addWatermark(string $imagePath, ?string $watermarkText = null): bool
    {
        // Проверяем включены ли водяные знаки
        if (!config('app.watermark_enabled', true)) {
            Log::info("⚠️ Водяные знаки отключены в настройках");
            return true;
        }

        try {
            // Получаем текст из переменной окружения или APP_NAME
            if (!$watermarkText) {
                $watermarkText = config('app.watermark_text', config('app.name', 'im-edok.ru'));
            }

            $fullPath = storage_path('app/public/' . $imagePath);
            
            // Проверяем существование файла
            if (!file_exists($fullPath)) {
                Log::warning("⚠️ Файл не найден для добавления водяного знака: {$fullPath}");
                return false;
            }

            // Определяем тип изображения
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                Log::warning("⚠️ Не удалось определить тип изображения: {$fullPath}");
                return false;
            }

            $mimeType = $imageInfo['mime'];
            
            // Загружаем изображение в зависимости от типа
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($fullPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($fullPath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($fullPath);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($fullPath);
                    break;
                default:
                    Log::warning("⚠️ Неподдерживаемый тип изображения: {$mimeType}");
                    return false;
            }

            if (!$image) {
                Log::error("❌ Не удалось загрузить изображение: {$fullPath}");
                return false;
            }

            // Включаем прозрачность для PNG
            imagealphablending($image, true);
            imagesavealpha($image, true);

            // Получаем размеры изображения
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Рассчитываем размер шрифта (2.5% от ширины изображения)
            $fontSize = max(12, (int)($width * 0.025));
            
            // Позиция водяного знака: правый нижний угол
            $padding = 15;
            
            // Используем встроенный шрифт GD (5 - самый большой встроенный)
            // Для лучшего результата можно использовать TTF шрифт
            $fontFile = $this->findSystemFont();
            
            if ($fontFile) {
                // Используем TrueType шрифт
                $textBox = imagettfbbox($fontSize, 0, $fontFile, $watermarkText);
                $textWidth = abs($textBox[4] - $textBox[0]);
                $textHeight = abs($textBox[5] - $textBox[1]);
                
                $x = $width - $textWidth - $padding;
                $y = $height - $padding;
                
                // Создаем цвета с прозрачностью
                // Тень (черная, 50% прозрачность = 64 в alpha канале, где 127 = полностью прозрачный)
                $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 64);
                // Основной текст (белый, 30% прозрачность = 38 в alpha канале)
                $textColor = imagecolorallocatealpha($image, 255, 255, 255, 38);
                
                // Рисуем тень (смещение на 2px)
                imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $shadowColor, $fontFile, $watermarkText);
                
                // Рисуем основной текст
                imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $watermarkText);
                
            } else {
                // Используем встроенный шрифт если TTF не найден
                $fontId = 5; // Самый большой встроенный шрифт
                $textWidth = imagefontwidth($fontId) * strlen($watermarkText);
                $textHeight = imagefontheight($fontId);
                
                $x = $width - $textWidth - $padding;
                $y = $height - $textHeight - $padding;
                
                // Тень
                $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 64);
                imagestring($image, $fontId, $x + 1, $y + 1, $watermarkText, $shadowColor);
                
                // Основной текст
                $textColor = imagecolorallocatealpha($image, 255, 255, 255, 38);
                imagestring($image, $fontId, $x, $y, $watermarkText, $textColor);
            }
            
            // Сохраняем изображение
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($image, $fullPath, 90);
                    break;
                case 'image/png':
                    imagepng($image, $fullPath, 8);
                    break;
                case 'image/gif':
                    imagegif($image, $fullPath);
                    break;
                case 'image/webp':
                    imagewebp($image, $fullPath, 90);
                    break;
            }
            
            // Освобождаем память
            imagedestroy($image);
            
            Log::info("✅ Водяной знак добавлен: {$imagePath}");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Ошибка добавления водяного знака: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Поиск системного шрифта для использования
     *
     * @return string|null
     */
    protected function findSystemFont(): ?string
    {
        // Возможные пути к шрифтам в Windows
        $windowsFonts = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/calibri.ttf',
            'C:/Windows/Fonts/verdana.ttf',
        ];
        
        // Возможные пути к шрифтам в Linux
        $linuxFonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        ];
        
        $allFonts = array_merge($windowsFonts, $linuxFonts);
        
        foreach ($allFonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }
        
        return null;
    }
}
