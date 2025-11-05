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
    protected string $baseUrl = 'https://food.ru';
    protected int $recipesPerPage = 40; // Количество рецептов на странице food.ru (не 48!)
    
    // Категории для парсинга
    protected array $categories = [
        'ot-redakcii-foodru/dostupnyi-zozh' => 'Доступный ЗОЖ',
        'zakuski' => 'Закуски',
        'salaty' => 'Салаты',
        'pervye-bliuda' => 'Первые блюда',
        'vtorye-bliuda' => 'Вторые блюда',
        'garniry' => 'Гарниры',
        'deserty' => 'Десерты',
        'vypechka' => 'Выпечка',
        'napitki' => 'Напитки',
        'zagotovki' => 'Заготовки и консервы',
        'sousy-i-marinady' => 'Соусы и маринады',
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
     * На food.ru первая страница БЕЗ ?page=1, начинается с ?page=2
     *
     * @param string $category Slug категории (zakuski, salaty и т.д.)
     * @param int $page Номер страницы (первая страница = 1)
     * @return array Массив URL рецептов
     */
    public function parseRecipesList(string $category, int $page = 1): array
    {
        try {
            // На food.ru первая страница БЕЗ параметра ?page=1
            // Страницы начинаются с ?page=2
            if ($page === 1) {
                $pageUrl = "{$this->baseUrl}/recipes/{$category}";
            } else {
                $pageUrl = "{$this->baseUrl}/recipes/{$category}?page={$page}";
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
     * Парсит HTML-страницу food.ru с помощью DOMXPath
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

            // Ищем все ссылки на рецепты в карточках
            // Структура: <a class="card_cardLink__EUMlQ" href="/recipes/263641-tvorozhnaja-zapekanka-s-jagodamI">
            $nodes = $xpath->query('//a[contains(@class, "card_cardLink")]/@href');
            
            if ($nodes && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $href = $node->nodeValue;
                    
                    // Проверяем, что это действительно ссылка на рецепт
                    if ($href && str_starts_with($href, '/recipes/') && preg_match('/\/recipes\/\d+/', $href)) {
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
     * Использует пагинацию с параметром ?page=
     * ВАЖНО: Возвращает генератор для постраничной обработки
     *
     * @param string $category Slug категории (zakuski, salaty и т.д.)
     * @param int $targetCount Целевое количество НОВЫХ рецептов
     * @param int $startPage Начальная страница (по умолчанию 1)
     * @param callable|null $callback Колбэк для обработки каждой порции URL (для добавления в очередь сразу)
     * @return array Массив URL новых рецептов
     */
    public function parseMultiplePages(string $category, int $targetCount = 30, int $startPage = 1, ?callable $callback = null): array
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
                
                // ✅ ВЫЗЫВАЕМ CALLBACK СРАЗУ для добавления в очередь
                if ($callback && !empty($toAdd)) {
                    $callback($toAdd, $currentPage);
                }
                
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
