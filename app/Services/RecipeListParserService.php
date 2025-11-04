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
    protected string $baseUrl = 'https://1000.menu';
    protected int $recipesPerScroll = 20; // Количество рецептов, загружаемых за один "скролл"

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
     * Получить список URL рецептов с одной конкретной страницы
     * Использует разные разделы сайта для получения большего разнообразия
     *
     * @param int $page Номер страницы
     * @return array Массив URL рецептов
     */
    public function parseRecipesList(int $page = 1): array
    {
        try {
            // Разные разделы сайта для парсинга
            $sections = [
                '/cooking',           // Все рецепты
                '/cooking/new',       // Новые рецепты
                '/cooking/popular',   // Популярные
                '/catalog',           // Каталог
            ];
            
            // Циклически выбираем раздел
            $sectionIndex = ($page - 1) % count($sections);
            $section = $sections[$sectionIndex];
            $actualPage = (int)ceil($page / count($sections));
            
            // Формируем URL
            $pageUrl = $this->baseUrl . $section;
            if ($actualPage > 1) {
                $pageUrl .= '?page=' . $actualPage;
            }

            Log::info("🔍 Парсинг страницы {$page} (раздел: {$section}, стр.{$actualPage}): {$pageUrl}");

            $recipes = $this->fetchRecipesFromUrl($pageUrl);
            
            Log::info("✅ Страница {$page}: найдено " . count($recipes) . " рецептов");

            return $recipes;

        } catch (\Exception $e) {
            Log::error("❌ Ошибка парсинга страницы {$page}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить рецепты из конкретного URL
     *
     * @param string $url URL для парсинга
     * @return array Массив URL рецептов
     */
    protected function fetchRecipesFromUrl(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            
            $recipeUrls = [];

            // Используем регулярные выражения для поиска ссылок на рецепты
            // Ищем ссылки типа /cooking/ЧИСЛО через теги <a>
            preg_match_all('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/', $html, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $href) {
                    // Фильтруем только ссылки на рецепты (с цифрами)
                    if (preg_match('/\/cooking\/(\d+)/', $href, $idMatch)) {
                        // Очищаем URL от фрагмента и параметров
                        $href = preg_replace('/[#?].*$/', '', $href);
                        $fullUrl = $this->baseUrl . $href;
                        
                        if (!in_array($fullUrl, $recipeUrls)) {
                            $recipeUrls[] = $fullUrl;
                        }
                    }
                }
            }

            return $recipeUrls;

        } catch (\Exception $e) {
            Log::warning("⚠️ Ошибка получения рецептов с {$url}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Собрать точное количество НОВЫХ рецептов (которых еще нет в базе)
     * Автоматически фильтрует уже существующие в базе рецепты
     *
     * @param int $targetCount Целевое количество НОВЫХ рецептов
     * @return array Массив URL новых рецептов
     */
    public function parseMultiplePages(int $targetCount = 30): array
    {
        Log::info("🎯 Задача: найти {$targetCount} НОВЫХ рецептов (которых нет в БД)");
        
        $newRecipes = [];
        $currentPage = 1;
        $maxPages = 100; // Максимум страниц для защиты от бесконечного цикла
        $totalChecked = 0;
        $emptyPagesCount = 0;
        $maxEmptyPages = 5; // Если 5 страниц подряд пустые - останавливаемся
        
        while (count($newRecipes) < $targetCount && $currentPage <= $maxPages) {
            // Получаем все URL с текущей страницы
            $pageRecipes = $this->parseRecipesList($currentPage);
            
            if (empty($pageRecipes)) {
                $emptyPagesCount++;
                Log::warning("⚠️ Страница {$currentPage} пустая ({$emptyPagesCount}/{$maxEmptyPages})");
                
                if ($emptyPagesCount >= $maxEmptyPages) {
                    Log::warning("⚠️ {$maxEmptyPages} пустых страниц подряд - останавливаем парсинг");
                    break;
                }
                
                $currentPage++;
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
            sleep(2); // Задержка между страницами
        }
        
        Log::info("🏁 Итого собрано НОВЫХ рецептов: " . count($newRecipes) . "/{$targetCount}");
        Log::info("📈 Всего проверено URL: {$totalChecked}");
        Log::info("📄 Просмотрено страниц: {$currentPage}");
        
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
     * Установить количество рецептов на один "скролл"
     *
     * @param int $count Количество рецептов
     * @return self
     */
    public function setRecipesPerScroll(int $count): self
    {
        $this->recipesPerScroll = $count;
        return $this;
    }
}
