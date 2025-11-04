<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Category;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeParserService
{
    protected Client $client;
    protected string $baseUrl = 'https://1000.menu';

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
                'Accept-Encoding' => 'gzip, deflate',
            ]
        ]);
    }

    /**
     * Парсинг отдельного рецепта по URL
     *
     * @param string $url URL рецепта
     * @return Recipe|null
     */
    public function parseRecipe(string $url): ?Recipe
    {
        try {
            Log::info("Парсинг рецепта: {$url}");

            // Проверяем, не существует ли уже такой рецепт
            if (Recipe::where('source_url', $url)->exists()) {
                Log::info("Рецепт уже существует: {$url}");
                return null;
            }

            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();

            // Извлекаем данные согласно ТЗ
            $title = $this->parseTitle($html);
            $description = $this->parseDescription($html);
            
            $data = [
                'title' => $title,
                'slug' => $this->generateSlug($title, $url),
                'meta_title' => $this->parseMetaTitle($html, $title),
                'meta_description' => $this->parseMetaDescription($html, $description),
                'meta_keywords' => $this->parseMetaKeywords($html, $title),
                'canonical_url' => $url,
                'description' => $description,
                'image_path' => $this->downloadImage($html),
                'og_image' => $this->parseOgImage($html),
                'ingredients' => $this->parseIngredients($html),
                'steps' => $this->parseSteps($html),
                'nutrition' => $this->parseNutrition($html),
                'prep_time' => $this->parsePrepTime($html),
                'cook_time' => $this->parseCookTime($html),
                'total_time' => $this->parseTotalTime($html),
                'servings' => $this->parseServings($html),
                'difficulty' => $this->parseDifficulty($html),
                'rating' => $this->parseRating($html),
                'rating_count' => $this->parseRatingCount($html),
                'source_url' => $url,
                'views' => $this->parseViews($html),
                'likes' => $this->parseLikes($html),
                'dislikes' => $this->parseDislikes($html),
            ];

            // Создаем рецепт
            $recipe = Recipe::create($data);
            
            // Парсим и привязываем категории
            $this->attachCategories($recipe, $html);
            
            Log::info("Рецепт успешно создан: {$data['title']}");

            return $recipe;

        } catch (\Exception $e) {
            Log::error("Ошибка парсинга рецепта {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Парсинг названия рецепта
     */
    protected function parseTitle(string $html): string
    {
        if (preg_match('/<h1[^>]*itemprop="name"[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
            return strip_tags(trim($matches[1]));
        }
        return 'Без названия';
    }

    /**
     * Парсинг описания рецепта
     */
    protected function parseDescription(string $html): ?string
    {
        if (preg_match('/<div[^>]*class="[^"]*description[^"]*"[^>]*itemprop="description"[^>]*>.*?<span[^>]*class="description-text"[^>]*>(.*?)<\/span>/is', $html, $matches)) {
            return strip_tags(trim($matches[1]));
        }
        return null;
    }

    /**
     * Скачивание и сохранение изображения
     */
    protected function downloadImage(string $html): ?string
    {
        try {
            // Пробуем найти изображение в img с itemprop="image"
            if (preg_match('/<img[^>]+itemprop=["\']image["\'][^>]+src=["\']([^"\']+)["\']/', $html, $matches)) {
                $imageUrl = $matches[1];
            } 
            // Если не нашли, ищем в og:image
            elseif (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/', $html, $matches)) {
                $imageUrl = $matches[1];
            }
            else {
                return null;
            }
            
            // Добавляем https: если URL начинается с //
            if (str_starts_with($imageUrl, '//')) {
                $imageUrl = 'https:' . $imageUrl;
            }

            Log::info("Скачивание изображения: {$imageUrl}");

            $response = $this->client->get($imageUrl);
            $imageContent = $response->getBody()->getContents();

            // Генерируем имя файла
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = Str::random(40) . '.' . $extension;
            $path = 'recipes/' . $filename;

            // Сохраняем в public/storage/recipes
            Storage::disk('public')->put($path, $imageContent);

            return $path;

        } catch (\Exception $e) {
            Log::error("Ошибка скачивания изображения: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Парсинг ингредиентов
     */
    protected function parseIngredients(string $html): array
    {
        $ingredients = [];

        try {
            // Ищем все meta теги с recipeIngredient
            preg_match_all('/<meta[^>]+itemprop=["\']recipeIngredient["\'][^>]+content=["\']([^"\']+)["\']/', $html, $matches);
            
            foreach ($matches[1] as $ingredientText) {
                // Разбираем строку типа "Фарш мясной - 500 гр"
                if (preg_match('/^(.+?)\s*-\s*(.+)$/', $ingredientText, $parts)) {
                    $name = trim($parts[1]);
                    $quantityAndMeasure = trim($parts[2]);
                    
                    // Разделяем количество и единицу измерения
                    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(.*)$/', $quantityAndMeasure, $qm)) {
                        $quantity = str_replace(',', '.', $qm[1]);
                        $measure = trim($qm[2]);
                    } else {
                        $quantity = '';
                        $measure = $quantityAndMeasure;
                    }
                    
                    $ingredients[] = [
                        'name' => $name,
                        'quantity' => $quantity,
                        'measure' => $measure
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Ошибка парсинга ингредиентов: " . $e->getMessage());
        }

        return $ingredients;
    }

    /**
     * Парсинг шагов приготовления
     */
    protected function parseSteps(string $html): array
    {
        $steps = [];

        try {
            // Ищем блок с инструкциями
            if (preg_match('/<ol[^>]+class=["\']instructions["\'][^>]*>(.*?)<\/ol>/is', $html, $olMatch)) {
                $instructionsHtml = $olMatch[1];
                
                // Ищем все элементы li
                preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $instructionsHtml, $liMatches);
                
                $index = 1;
                foreach ($liMatches[1] as $stepHtml) {
                    // Пропускаем рекламные блоки
                    if (stripos($stepHtml, 'as-ad-step') !== false || stripos($stepHtml, 'adfox') !== false) {
                        continue;
                    }
                    
                    $description = '';
                    $image = null;

                    // Описание шага из p.instruction
                    if (preg_match('/<p[^>]+class=["\']instruction["\'][^>]*>(.*?)<\/p>/is', $stepHtml, $descrMatch)) {
                        $description = strip_tags(trim($descrMatch[1]));
                    }

                    // Изображение шага
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $stepHtml, $imgMatch)) {
                        $imageSrc = $imgMatch[1];
                        if (str_starts_with($imageSrc, '//')) {
                            $image = 'https:' . $imageSrc;
                        } else {
                            $image = $imageSrc;
                        }
                    }

                    if ($description) {
                        $steps[] = [
                            'step_number' => $index,
                            'description' => $description,
                            'image' => $image
                        ];
                        $index++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Ошибка парсинга шагов: " . $e->getMessage());
        }

        return $steps;
    }

    /**
     * Парсинг информации о питательности
     */
    protected function parseNutrition(string $html): array
    {
        $nutrition = [];

        try {
            // Калории
            if (preg_match('/<span[^>]*id="nutr_kcal"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['calories'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['calories'] = '0';
            }

            // Белки
            if (preg_match('/<span[^>]*id="nutr_p"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['proteins'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['proteins'] = '0';
            }

            // Жиры
            if (preg_match('/<span[^>]*id="nutr_f"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['fats'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['fats'] = '0';
            }

            // Углеводы
            if (preg_match('/<span[^>]*id="nutr_c"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['carbs'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['carbs'] = '0';
            }

            // Проценты белков
            if (preg_match('/<span[^>]*id="nutr_ratio_p"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['proteins_percent'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['proteins_percent'] = '0';
            }

            // Проценты жиров
            if (preg_match('/<span[^>]*id="nutr_ratio_f"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['fats_percent'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['fats_percent'] = '0';
            }

            // Проценты углеводов
            if (preg_match('/<span[^>]*id="nutr_ratio_c"[^>]*>(.*?)<\/span>/is', $html, $match)) {
                $nutrition['carbs_percent'] = strip_tags(trim($match[1]));
            } else {
                $nutrition['carbs_percent'] = '0';
            }

        } catch (\Exception $e) {
            Log::error("Ошибка парсинга питательности: " . $e->getMessage());
        }

        return $nutrition;
    }

    /**
     * Парсинг количества просмотров
     */
    protected function parseViews(string $html): int
    {
        try {
            // Ищем span с title="Просмотров" и внутри него span с классом label
            if (preg_match('/<span[^>]+title=["\']Просмотров["\'][^>]*>.*?<span[^>]+class=["\']label[^"\']*["\'][^>]*>(.*?)<\/span>/is', $html, $match)) {
                $viewsText = strip_tags(trim($match[1]));
                // Убираем пробелы из числа (267 276 -> 267276)
                return (int) str_replace(' ', '', $viewsText);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка парсинга просмотров: " . $e->getMessage());
        }
        return 0;
    }

    /**
     * Парсинг лайков
     */
    protected function parseLikes(string $html): int
    {
        try {
            // Ищем span с классом "type like" и внутри него a с классом review-points
            if (preg_match('/<span[^>]+class=["\'][^"\']*type like[^"\']*["\'][^>]*>.*?<a[^>]+class=["\'][^"\']*review-points[^"\']*["\'][^>]*>\s*(\d+)\s*<\/a>/is', $html, $match)) {
                return (int) trim($match[1]);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка парсинга лайков: " . $e->getMessage());
        }
        return 0;
    }

    /**
     * Генерация slug из URL и названия
     */
    protected function generateSlug(string $title, string $url): string
    {
        // Пытаемся извлечь slug из URL
        if (preg_match('/\/cooking\/\d+-(.+)$/', $url, $matches)) {
            return $matches[1];
        }
        
        // Если не получилось, создаем из названия
        return Str::slug($title);
    }

    /**
     * Парсинг meta title из HTML
     */
    protected function parseMetaTitle(string $html, string $defaultTitle): ?string
    {
        // Ищем meta property="og:title"
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return trim($matches[1]);
        }
        
        // Ищем тег <title>
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(strip_tags($matches[1]));
        }
        
        return $defaultTitle . ' - Рецепт приготовления с фото';
    }

    /**
     * Парсинг meta description
     */
    protected function parseMetaDescription(string $html, ?string $defaultDescription): ?string
    {
        // Ищем meta name="description"
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return trim($matches[1]);
        }
        
        // Ищем meta property="og:description"
        if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return trim($matches[1]);
        }
        
        // Если есть описание из парсинга, обрезаем до 160 символов
        if ($defaultDescription) {
            return mb_substr($defaultDescription, 0, 160);
        }
        
        return null;
    }

    /**
     * Парсинг meta keywords
     */
    protected function parseMetaKeywords(string $html, string $title): ?string
    {
        // Ищем meta name="keywords"
        if (preg_match('/<meta[^>]+name=["\']keywords["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return trim($matches[1]);
        }
        
        // Генерируем из названия
        $keywords = [];
        $keywords[] = $title;
        $keywords[] = 'рецепт';
        $keywords[] = 'приготовление';
        $keywords[] = 'с фото';
        
        return implode(', ', $keywords);
    }

    /**
     * Парсинг Open Graph изображения
     */
    protected function parseOgImage(string $html): ?string
    {
        // Ищем meta property="og:image"
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    /**
     * Парсинг времени подготовки
     */
    protected function parsePrepTime(string $html): ?int
    {
        // Ищем время подготовки в микроданных
        if (preg_match('/<meta[^>]+itemprop=["\']prepTime["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $this->parseIsoDuration($matches[1]);
        }
        
        return null;
    }

    /**
     * Парсинг времени приготовления
     */
    protected function parseCookTime(string $html): ?int
    {
        // Ищем время приготовления в микроданных
        if (preg_match('/<meta[^>]+itemprop=["\']cookTime["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $this->parseIsoDuration($matches[1]);
        }
        
        return null;
    }

    /**
     * Парсинг общего времени
     */
    protected function parseTotalTime(string $html): ?int
    {
        // Ищем общее время в микроданных (формат ISO 8601: PT2H, PT30M, PT1H30M)
        if (preg_match('/<meta[^>]+itemprop=["\']totalTime["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $this->parseIsoDuration($matches[1]);
        }
        
        // Если не найдено в meta, ищем в span с классом duration
        if (preg_match('/<span[^>]+class=["\']duration["\'][^>]*>([^<]+)<\/span>/i', $html, $matches)) {
            return $this->parseIsoDuration(trim($matches[1]));
        }
        
        // Если есть prep_time и cook_time, суммируем
        $prepTime = $this->parsePrepTime($html);
        $cookTime = $this->parseCookTime($html);
        
        if ($prepTime && $cookTime) {
            return $prepTime + $cookTime;
        }
        
        return null;
    }

    /**
     * Парсинг ISO 8601 duration в минуты
     * Поддерживает форматы: PT2H, PT30M, PT1H30M, PT90M
     */
    protected function parseIsoDuration(string $duration): ?int
    {
        if (!preg_match('/^PT/', $duration)) {
            return null;
        }

        $totalMinutes = 0;

        // Парсим часы (например, PT2H или PT1H30M)
        if (preg_match('/(\d+)H/', $duration, $matches)) {
            $totalMinutes += (int) $matches[1] * 60;
        }

        // Парсим минуты (например, PT30M или PT1H30M)
        if (preg_match('/(\d+)M/', $duration, $matches)) {
            $totalMinutes += (int) $matches[1];
        }

        return $totalMinutes > 0 ? $totalMinutes : null;
    }

    /**
     * Парсинг количества порций
     */
    protected function parseServings(string $html): ?int
    {
        // Ищем количество порций в микроданных
        if (preg_match('/<meta[^>]+itemprop=["\']recipeYield["\'][^>]+content=["\'](\d+)["\'][^>]*>/i', $html, $matches)) {
            return (int) $matches[1];
        }
        
        // Ищем в тексте "на N порций"
        if (preg_match('/на\s+(\d+)\s+порц/iu', $html, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    /**
     * Парсинг сложности рецепта
     */
    protected function parseDifficulty(string $html): ?string
    {
        // Ищем уровень сложности
        if (preg_match('/сложность["\'\s:>]*([а-яё]+)/iu', $html, $matches)) {
            $difficulty = mb_strtolower(trim($matches[1]));
            
            if (in_array($difficulty, ['легкий', 'простой', 'легко'])) {
                return 'easy';
            } elseif (in_array($difficulty, ['средний', 'средняя'])) {
                return 'medium';
            } elseif (in_array($difficulty, ['сложный', 'трудный', 'сложно'])) {
                return 'hard';
            }
        }
        
        return 'medium'; // По умолчанию средняя сложность
    }

    /**
     * Парсинг рейтинга
     */
    protected function parseRating(string $html): float
    {
        // Ищем рейтинг в микроданных
        if (preg_match('/<meta[^>]+itemprop=["\']ratingValue["\'][^>]+content=["\']([0-9.]+)["\'][^>]*>/i', $html, $matches)) {
            $rating = (float) $matches[1];
            return min(5.0, max(0.0, $rating)); // Ограничиваем от 0 до 5
        }
        
        // Ищем в другом формате
        if (preg_match('/рейтинг["\'\s:>]*([0-9.]+)/iu', $html, $matches)) {
            $rating = (float) str_replace(',', '.', $matches[1]);
            return min(5.0, max(0.0, $rating));
        }
        
        return 0.0;
    }

    /**
     * Парсинг количества оценок
     */
    protected function parseRatingCount(string $html): int
    {
        // Ищем количество оценок в микроданных
        if (preg_match('/<meta[^>]+itemprop=["\']ratingCount["\'][^>]+content=["\'](\d+)["\'][^>]*>/i', $html, $matches)) {
            return (int) $matches[1];
        }
        
        // Ищем в тексте "N оценок"
        if (preg_match('/(\d+)\s+оценок/iu', $html, $matches)) {
            return (int) $matches[1];
        }
        
        return 0;
    }

    /**
     * Парсинг дизлайков
     */
    protected function parseDislikes(string $html): int
    {
        try {
            // Ищем span с классом "type dislike" и внутри него a с классом review-points
            if (preg_match('/<span[^>]+class=["\'][^"\']*type dislike[^"\']*["\'][^>]*>.*?<a[^>]+class=["\'][^"\']*review-points[^"\']*["\'][^>]*>\s*(\d+)\s*<\/a>/is', $html, $match)) {
                return (int) trim($match[1]);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка парсинга дизлайков: " . $e->getMessage());
        }
        return 0;
    }

    /**
     * Парсинг категорий из breadcrumbs
     */
    protected function parseCategories(string $html): array
    {
        $categories = [];
        
        try {
            Log::info("🔍 Начинаем парсинг категорий из breadcrumbs");
            
            // Ищем breadcrumbs - пробуем несколько вариантов
            $breadcrumbsHtml = '';
            
            // Вариант 1: стандартный breadcrumbs
            if (preg_match('/<ol[^>]+class=["\'][^"\']*breadcrumbs[^"\']*["\'][^>]*>(.*?)<\/ol>/is', $html, $breadcrumbsMatch)) {
                $breadcrumbsHtml = $breadcrumbsMatch[1];
                Log::info("✅ Breadcrumbs найдены (вариант 1)");
                
                // Сохраняем breadcrumbs в файл для отладки (только первый раз)
                $debugFile = storage_path('logs/breadcrumbs_debug.html');
                if (!file_exists($debugFile)) {
                    file_put_contents($debugFile, $breadcrumbsHtml);
                    Log::info("📝 Breadcrumbs HTML сохранен в: " . $debugFile);
                }
            }
            // Вариант 2: BreadcrumbList в schema.org
            elseif (preg_match('/<ol[^>]+itemtype=["\'].*?BreadcrumbList[^"\']*["\'][^>]*>(.*?)<\/ol>/is', $html, $breadcrumbsMatch)) {
                $breadcrumbsHtml = $breadcrumbsMatch[1];
                Log::info("✅ Breadcrumbs найдены (вариант 2 - schema.org)");
            }
            
            if (empty($breadcrumbsHtml)) {
                Log::warning("⚠️ Breadcrumbs не найдены в HTML");
                return [];
            }
            
            // Извлекаем все элементы breadcrumb - несколько вариантов парсинга
            $categoryNames = [];
            
            // Извлекаем все <li> элементы с itemprop="itemListElement"
            // Важно: <li> теги могут быть не закрыты в HTML!
            if (preg_match_all('/<li[^>]*itemprop=["\']itemListElement["\'][^>]*>.*?(?=<li|$)/is', $breadcrumbsHtml, $liMatches)) {
                Log::info("🔍 Найдено <li> элементов: " . count($liMatches[0]));
                
                foreach ($liMatches[0] as $liHtml) {
                    Log::info("🔎 Обрабатываем элемент: " . mb_substr($liHtml, 0, 100) . "...");
                    
                    // Пропускаем элементы с class="hidden" (это обычно название рецепта)
                    if (preg_match('/class=["\'][^"\']*hidden[^"\']*["\']/', $liHtml)) {
                        Log::info("⏭️ Пропускаем скрытый элемент");
                        continue;
                    }
                    
                    // Извлекаем текст из <span itemprop="name">
                    if (preg_match('/<span[^>]*itemprop=["\']name["\'][^>]*>([^<]+)<\/span>/is', $liHtml, $nameMatch)) {
                        $name = strip_tags(trim($nameMatch[1]));
                        
                        Log::info("🔤 Найден текст в span: '{$name}'");
                        
                        // Пропускаем "Главная" и пустые значения
                        if ($name && $name !== 'Главная' && $name !== 'главная' && mb_strlen($name) > 2) {
                            $categoryNames[] = $name;
                            Log::info("✅ Найдена категория: {$name}");
                        } else {
                            Log::info("⏭️ Пропускаем: '{$name}'");
                        }
                    } else {
                        Log::warning("⚠️ Не найден span itemprop='name' в элементе");
                    }
                }
            } else {
                Log::warning("⚠️ Не найдено ни одного <li> элемента");
            }
            
            // Убираем дубликаты
            $categoryNames = array_values(array_unique($categoryNames));

            // Попробуем удалить название самого рецепта, если оно попало в список
            try {
                $pageTitle = $this->parseTitle($html);
                if ($pageTitle) {
                    // Удаляем элементы, совпадающие с заголовком рецепта или содержащие его
                    $categoryNames = array_filter($categoryNames, function ($n) use ($pageTitle) {
                        $nTrim = mb_strtolower(trim($n));
                        $tTrim = mb_strtolower(trim($pageTitle));
                        if ($nTrim === $tTrim) {
                            return false;
                        }
                        if (mb_stripos($nTrim, $tTrim) !== false || mb_stripos($tTrim, $nTrim) !== false) {
                            return false;
                        }
                        return true;
                    });

                    // Переиндексируем массив
                    $categoryNames = array_values($categoryNames);
                }
            } catch (\Exception $e) {
                // Если по какой-то причине не получилось получить заголовок — ничего критичного
                Log::warning("Не удалось сравнить с заголовком рецепта: " . $e->getMessage());
            }
            
            // Формируем результат (сохраняем порядок)
            foreach (array_values($categoryNames) as $index => $name) {
                $categories[] = [
                    'name' => $name,
                    'position' => $index,
                ];
            }
            
            if (!empty($categories)) {
                Log::info("✅ Найдено категорий: " . count($categories) . " - " . implode(', ', array_column($categories, 'name')));
            } else {
                Log::warning("⚠️ Категории не извлечены из breadcrumbs");
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Ошибка парсинга категорий: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
        
        return $categories;
    }

    /**
     * Привязка категорий к рецепту
     */
    protected function attachCategories(Recipe $recipe, string $html): void
    {
        try {
            $parsedCategories = $this->parseCategories($html);
            
            if (empty($parsedCategories)) {
                Log::warning("⚠️ Категории не найдены для рецепта: {$recipe->title}");
                return;
            }

            Log::info("🏷️ Привязываем категории к рецепту: {$recipe->title}");

            $categoryIds = [];
            $parentCategory = null;

            foreach ($parsedCategories as $index => $categoryData) {
                // Создаем или получаем категорию
                $category = Category::firstOrCreate(
                    ['name' => $categoryData['name']],
                    [
                        'slug' => Str::slug($categoryData['name']),
                        'parent_id' => $parentCategory ? $parentCategory->id : null,
                    ]
                );

                $categoryIds[] = $category->id;
                
                Log::info("📁 Категория '{$category->name}' (ID: {$category->id}, Parent: " . ($parentCategory ? $parentCategory->name : 'нет') . ")");
                
                // Сохраняем для следующей итерации (следующая категория будет дочерней)
                $parentCategory = $category;
            }

            // Привязываем все категории к рецепту
            if (!empty($categoryIds)) {
                $recipe->categories()->sync($categoryIds);
                
                // Обновляем счетчики рецептов для всех категорий
                foreach ($categoryIds as $categoryId) {
                    $category = Category::find($categoryId);
                    if ($category) {
                        $category->recipe_count = $category->recipes()->count();
                        $category->save();
                    }
                }
                
                Log::info("✅ К рецепту '{$recipe->title}' привязано категорий: " . count($categoryIds));
            }
        } catch (\Exception $e) {
            Log::error("❌ Ошибка привязки категорий к рецепту {$recipe->title}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
