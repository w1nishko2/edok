<?php

namespace App\Services;

use App\Models\Recipe;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class RecipeListParserService
{
    protected Client $client;
    protected string $baseUrl = 'https://povar.ru';
    protected int $recipesPerPage = 30; // Количество рецептов на странице
    
    // Категории для парсинга
    protected array $categories = [
        'meat' => 'Блюда из мяса',
        'fish' => 'Блюда из рыбы',
        'ptica' => 'Блюда из птицы',
        'vegies' => 'Блюда из овощей',
        'salad' => 'Салаты',
        'soup' => 'Супы',
        'vypechka' => 'Выпечка',
        'dessert' => 'Десерты',
    ];

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
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ]
        ]);
    }

    /**
     * Получить список URL рецептов с одной конкретной страницы категории
     * Использует нумерованную пагинацию: /list/meat/, /list/meat/2/, /list/meat/3/
     *
     * @param string $category Slug категории (meat, fish, ptica и т.д.)
     * @param int $page Номер страницы (первая страница = 1, НО URL без номера!)
     * @return array Массив URL рецептов
     */
    public function parseRecipesList(string $category, int $page = 1): array
    {
        try {
            // Формируем URL: первая страница БЕЗ номера, остальные - с номером
            $pageUrl = $this->baseUrl . '/list/' . $category . '/';
            if ($page > 1) {
                $pageUrl .= $page . '/';
            }

            Log::info("🔍 Парсинг категории '{$category}', страница {$page}: {$pageUrl}");

            $recipes = $this->fetchRecipesFromUrl($pageUrl);
            
            Log::info("✅ Страница {$page}: найдено " . count($recipes) . " рецептов");

            return $recipes;

        } catch (\Exception $e) {
            Log::error("❌ Ошибка парсинга категории '{$category}', страница {$page}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить рецепты из конкретного URL
     * Парсит HTML-страницу povar.ru с помощью DOMXPath
     *
     * @param string $url URL для парсинга
     * @return array Массив URL рецептов
     */
    protected function fetchRecipesFromUrl(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            
            // Подавляем ошибки парсинга HTML
            libxml_use_internal_errors(true);
            
            $dom = new DOMDocument();
            $dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            $recipeUrls = [];

            // Ищем все ссылки на рецепты: <a href="/recipes/salat_parij-73708.html" class="listRecipieTitle">
            $nodes = $xpath->query('//div[@class="recipe"]//a[@class="listRecipieTitle"]');
            
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $href = $node->getAttribute('href');
                    
                    // Проверяем, что это действительно ссылка на рецепт
                    if ($href && str_starts_with($href, '/recipes/')) {
                        $fullUrl = $this->baseUrl . $href;
                        
                        if (!in_array($fullUrl, $recipeUrls)) {
                            $recipeUrls[] = $fullUrl;
                        }
                    }
                }
            }

            libxml_clear_errors();
            return $recipeUrls;

        } catch (\Exception $e) {
            Log::warning("⚠️ Ошибка получения рецептов с {$url}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Собрать точное количество НОВЫХ рецептов из указанной категории
     * Автоматически фильтрует уже существующие в базе рецепты
     * Использует нумерованную пагинацию (1, 2, 3...)
     *
     * @param string $category Slug категории (meat, fish, ptica и т.д.)
     * @param int $targetCount Целевое количество НОВЫХ рецептов
     * @param int $startPage Начальная страница (по умолчанию 1, для meat можно начать с 2)
     * @return array Массив URL новых рецептов
     */
    public function parseMultiplePages(string $category, int $targetCount = 30, int $startPage = 1): array
    {
        Log::info("🎯 Задача: найти {$targetCount} НОВЫХ рецептов из категории '{$category}' (с {$startPage}-й страницы)");
        
        $newRecipes = [];
        $currentPage = $startPage;
        $maxIterations = 100; // Максимум итераций для защиты от бесконечного цикла
        $iterations = 0;
        $totalChecked = 0;
        $emptyPagesCount = 0;
        $maxEmptyPages = 5; // Если 5 страниц подряд пустые - останавливаемся
        
        while (count($newRecipes) < $targetCount && $iterations < $maxIterations) {
            // Получаем все URL с текущей страницы
            $pageRecipes = $this->parseRecipesList($category, $currentPage);
            
            if (empty($pageRecipes)) {
                $emptyPagesCount++;
                Log::warning("⚠️ Страница {$currentPage} пустая ({$emptyPagesCount}/{$maxEmptyPages})");
                
                if ($emptyPagesCount >= $maxEmptyPages) {
                    Log::warning("⚠️ {$maxEmptyPages} пустых страниц подряд - останавливаем парсинг");
                    break;
                }
                
                $currentPage++;
                $iterations++;
                sleep(2);
                continue;
            }
            
            $emptyPagesCount = 0; // Сбрасываем счетчик пустых страниц
            $totalChecked += count($pageRecipes);
            
            // Фильтруем - оставляем только те URL, которых НЕТ в базе
            $filtered = $this->filterExistingRecipes($pageRecipes);
            
            if (empty($filtered)) {
                Log::info("📊 Страница {$currentPage}: все " . count($pageRecipes) . " рецептов уже в БД (проверено {$totalChecked} URL)");
            } else {
                Log::info("📊 Страница {$currentPage}: из " . count($pageRecipes) . " рецептов, новых: " . count($filtered));
                
                // Добавляем новые рецепты (ровно столько, сколько нужно до цели)
                $needMore = $targetCount - count($newRecipes);
                $toAdd = array_slice($filtered, 0, $needMore);
                
                $newRecipes = array_merge($newRecipes, $toAdd);
                
                Log::info("✅ Собрано новых рецептов: " . count($newRecipes) . "/{$targetCount}");
                
                // Если достигли цели - выходим
                if (count($newRecipes) >= $targetCount) {
                    break;
                }
            }
            
            $currentPage++;
            $iterations++;
            sleep(rand(2, 4)); // Случайная задержка между страницами
        }
        
        Log::info("🏁 Итого собрано НОВЫХ рецептов: " . count($newRecipes) . "/{$targetCount}");
        Log::info("📈 Всего проверено URL: {$totalChecked}");
        Log::info("📄 Просмотрено итераций: {$iterations}");
        
        return $newRecipes;
    }

    /**
     * Фильтрует список URL, оставляя только те, которых нет в базе данных
     *
     * @param array $urls Массив URL для проверки
     * @return array Массив URL, которых нет в базе
     */
    protected function filterExistingRecipes(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        // Получаем все существующие URL из базы данных
        $existingUrls = Recipe::whereIn('source_url', $urls)
            ->pluck('source_url')
            ->toArray();

        // Возвращаем только новые URL
        $newUrls = array_diff($urls, $existingUrls);

        return array_values($newUrls); // Переиндексируем массив
    }

    /**
     * Получить все категории для парсинга
     *
     * @return array Массив категорий
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
