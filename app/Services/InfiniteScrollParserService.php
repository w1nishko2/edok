<?php

namespace App\Services;

use App\Models\Recipe;
use App\Services\RecipeParserService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InfiniteScrollParserService
{
    protected Client $client;
    protected string $baseUrl = 'https://1000.menu';
    protected string $targetUrl = 'https://1000.menu/cooking/all-new';
    protected RecipeParserService $recipeParser;
    protected int $batchSize = 5; // Количество рецептов для парсинга перед записью в БД
    
    public function __construct(RecipeParserService $recipeParser)
    {
        $this->recipeParser = $recipeParser;
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
     * Бесконечный парсинг с пагинацией
     * Находит новые рецепты, парсит их и записывает в БД партиями
     *
     * @param int $maxRecipes Максимальное количество рецептов для добавления (0 = бесконечно)
     * @param int $startOffset Начальный offset для пагинации
     * @return array Статистика выполнения
     */
    public function parseInfinitely(int $maxRecipes = 0, int $startOffset = 0): array
    {
        $stats = [
            'total_found' => 0,
            'total_new' => 0,
            'total_added' => 0,
            'total_failed' => 0,
            'urls_checked' => 0,
            'pages_processed' => 0,
        ];

        $offset = $startOffset;
        $limit = 20; // Количество рецептов на одну "подгрузку"
        $newUrlsBatch = [];
        $consecutiveEmpty = 0;
        $maxConsecutiveEmpty = 3;

        Log::info("🚀 Запуск бесконечного парсера", [
            'max_recipes' => $maxRecipes === 0 ? 'бесконечно' : $maxRecipes,
            'batch_size' => $this->batchSize,
            'start_offset' => $startOffset,
        ]);

        while (true) {
            // Проверяем лимит
            if ($maxRecipes > 0 && $stats['total_added'] >= $maxRecipes) {
                Log::info("✅ Достигнут лимит рецептов: {$maxRecipes}");
                break;
            }

            // Получаем URL рецептов с текущей "страницы"
            $urls = $this->fetchRecipeUrlsWithOffset($offset, $limit);
            $stats['pages_processed']++;
            $stats['urls_checked'] += count($urls);

            if (empty($urls)) {
                $consecutiveEmpty++;
                Log::warning("⚠️ Пустая страница #{$stats['pages_processed']} (offset: {$offset}), попыток: {$consecutiveEmpty}/{$maxConsecutiveEmpty}");
                
                if ($consecutiveEmpty >= $maxConsecutiveEmpty) {
                    Log::info("🛑 Достигнут конец доступных рецептов");
                    break;
                }
                
                $offset += $limit;
                sleep(2);
                continue;
            }

            $consecutiveEmpty = 0;

            // Фильтруем - оставляем только новые
            $newUrls = $this->filterExistingRecipes($urls);
            $stats['total_found'] += count($urls);
            $stats['total_new'] += count($newUrls);

            Log::info("📊 Страница #{$stats['pages_processed']} (offset: {$offset}): найдено {" . count($urls) . "}, новых {" . count($newUrls) . "}");

            // Добавляем новые URL в батч
            foreach ($newUrls as $url) {
                $newUrlsBatch[] = $url;

                // Когда набрали батч - парсим и сохраняем
                if (count($newUrlsBatch) >= $this->batchSize) {
                    $result = $this->parseBatch($newUrlsBatch);
                    $stats['total_added'] += $result['added'];
                    $stats['total_failed'] += $result['failed'];

                    Log::info("✅ Партия обработана: добавлено {$result['added']}, ошибок {$result['failed']}");
                    Log::info("📈 Общий прогресс: {$stats['total_added']} рецептов добавлено");

                    $newUrlsBatch = []; // Очищаем батч

                    // Проверяем лимит после каждого батча
                    if ($maxRecipes > 0 && $stats['total_added'] >= $maxRecipes) {
                        break 2; // Выходим из обоих циклов
                    }
                }
            }

            // Переходим к следующему offset
            $offset += $limit;
            
            // Пауза между запросами
            sleep(rand(2, 4));
        }

        // Обрабатываем оставшиеся URL в последнем батче
        if (!empty($newUrlsBatch)) {
            $result = $this->parseBatch($newUrlsBatch);
            $stats['total_added'] += $result['added'];
            $stats['total_failed'] += $result['failed'];
            Log::info("✅ Последняя партия обработана: добавлено {$result['added']}, ошибок {$result['failed']}");
        }

        Log::info("🏁 Парсинг завершен", $stats);

        return $stats;
    }

    /**
     * Получить URL рецептов с указанным offset
     *
     * @param int $offset Смещение
     * @param int $limit Количество
     * @return array Массив URL рецептов
     */
    protected function fetchRecipeUrlsWithOffset(int $offset, int $limit): array
    {
        try {
            // URL с параметрами пагинации
            $url = $this->targetUrl . "?offset={$offset}&limit={$limit}";
            
            Log::debug("🔍 Запрос: {$url}");

            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();

            $recipeUrls = [];

            // Ищем все ссылки на рецепты формата /cooking/ЧИСЛО
            preg_match_all('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $href) {
                    // Фильтруем только ссылки на рецепты
                    if (preg_match('/\/cooking\/(\d+)/', $href, $idMatch)) {
                        $recipeId = $idMatch[1];
                        
                        // Очищаем URL от параметров и фрагментов
                        $cleanUrl = preg_replace('/[#?].*$/', '', $href);
                        
                        // Формируем полный URL
                        if (strpos($cleanUrl, 'http') === 0) {
                            $fullUrl = $cleanUrl;
                        } else {
                            $fullUrl = $this->baseUrl . $cleanUrl;
                        }

                        // Добавляем только уникальные
                        if (!in_array($fullUrl, $recipeUrls)) {
                            $recipeUrls[] = $fullUrl;
                        }
                    }
                }
            }

            return array_values(array_unique($recipeUrls));

        } catch (\Exception $e) {
            Log::error("❌ Ошибка получения URL с offset {$offset}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Парсит партию URL и сохраняет рецепты в БД
     *
     * @param array $urls Массив URL для парсинга
     * @return array Результаты: ['added' => int, 'failed' => int]
     */
    protected function parseBatch(array $urls): array
    {
        $added = 0;
        $failed = 0;

        Log::info("🔄 Начало парсинга партии из " . count($urls) . " рецептов");

        foreach ($urls as $index => $url) {
            try {
                Log::debug("📖 Парсинг {" . ($index + 1) . "}/" . count($urls) . ": {$url}");

                $recipe = $this->recipeParser->parseRecipe($url);

                if ($recipe) {
                    $added++;
                    Log::debug("✅ Рецепт добавлен: {$recipe->title}");
                } else {
                    $failed++;
                    Log::warning("⚠️ Рецепт не был добавлен: {$url}");
                }

                // Пауза между парсингом рецептов
                sleep(rand(1, 2));

            } catch (\Exception $e) {
                $failed++;
                Log::error("❌ Ошибка парсинга {$url}: " . $e->getMessage());
            }
        }

        return [
            'added' => $added,
            'failed' => $failed,
        ];
    }

    /**
     * Фильтрует URL, оставляя только те, которых нет в БД
     *
     * @param array $urls Массив URL
     * @return array Массив новых URL
     */
    protected function filterExistingRecipes(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        $existingUrls = Recipe::whereIn('source_url', $urls)
            ->pluck('source_url')
            ->toArray();

        $newUrls = array_diff($urls, $existingUrls);

        return array_values($newUrls);
    }

    /**
     * Установить размер партии для обработки
     *
     * @param int $size Размер партии
     * @return self
     */
    public function setBatchSize(int $size): self
    {
        $this->batchSize = $size;
        return $this;
    }

    /**
     * Установить целевой URL для парсинга
     *
     * @param string $url URL страницы
     * @return self
     */
    public function setTargetUrl(string $url): self
    {
        $this->targetUrl = $url;
        return $this;
    }
}
