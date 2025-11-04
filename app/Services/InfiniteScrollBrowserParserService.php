<?php

namespace App\Services;

use App\Models\Recipe;
use Symfony\Component\Panther\Client;
use Illuminate\Support\Facades\Log;

class InfiniteScrollBrowserParserService
{
    protected string $baseUrl = 'https://1000.menu';
    protected string $targetUrl = 'https://1000.menu/cooking/all-new';
    
    /**
     * Парсинг страницы с бесконечной прокруткой используя headless браузер
     *
     * @param int $scrolls Количество прокруток (каждая прокрутка загружает ~20 рецептов)
     * @return array Массив URL рецептов
     */
    public function parseWithInfiniteScroll(int $scrolls = 10): array
    {
        try {
            Log::info("🚀 Запуск парсинга с infinite scroll", [
                'url' => $this->targetUrl,
                'scrolls' => $scrolls
            ]);

            // Создаем headless браузер
            $client = Client::createChromeClient(null, [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1920,1080'
            ]);

            // Открываем страницу
            $client->request('GET', $this->targetUrl);
            
            // Даем время на загрузку первых рецептов
            sleep(3);

            $allUrls = [];
            
            // Скроллим страницу вниз несколько раз
            for ($i = 0; $i < $scrolls; $i++) {
                Log::info("📜 Скролл #{$i}", ['total_urls' => count($allUrls)]);
                
                // Скроллим в самый низ страницы
                $client->executeScript('window.scrollTo(0, document.body.scrollHeight);');
                
                // Ждем загрузки новых рецептов
                sleep(2);
                
                // Собираем все ссылки на рецепты
                $crawler = $client->getCrawler();
                $links = $crawler->filter('a[href*="/cooking/"]')->each(function ($node) {
                    return $node->attr('href');
                });
                
                // Фильтруем только ссылки на рецепты (с цифрами)
                foreach ($links as $href) {
                    if (preg_match('/\/cooking\/(\d+)/', $href, $matches)) {
                        // Очищаем URL от фрагмента и параметров
                        $href = preg_replace('/[#?].*$/', '', $href);
                        $fullUrl = $this->baseUrl . $href;
                        
                        if (!in_array($fullUrl, $allUrls)) {
                            $allUrls[] = $fullUrl;
                        }
                    }
                }
                
                Log::info("📊 После скролла #{$i}: собрано " . count($allUrls) . " уникальных URL");
                
                // Если рецепты больше не добавляются - выходим
                if ($i > 0 && count($allUrls) === count($allUrls)) {
                    Log::info("⚠️ Новые рецепты больше не загружаются, останавливаем скролл");
                    break;
                }
            }

            $client->quit();

            Log::info("✅ Парсинг завершен", [
                'total_urls' => count($allUrls),
                'scrolls_made' => $scrolls
            ]);

            return $allUrls;

        } catch (\Exception $e) {
            Log::error("❌ Ошибка парсинга с infinite scroll: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Собрать определенное количество НОВЫХ рецептов
     *
     * @param int $targetCount Целевое количество новых рецептов
     * @return array Массив URL новых рецептов
     */
    public function collectNewRecipes(int $targetCount = 100): array
    {
        // Рассчитываем сколько скроллов нужно (~20 рецептов за скролл)
        $scrollsNeeded = (int)ceil($targetCount / 20) + 5; // +5 с запасом
        
        Log::info("🎯 Задача: собрать {$targetCount} новых рецептов", [
            'planned_scrolls' => $scrollsNeeded
        ]);

        // Парсим страницу
        $allUrls = $this->parseWithInfiniteScroll($scrollsNeeded);
        
        if (empty($allUrls)) {
            Log::warning("⚠️ Не удалось получить URL рецептов");
            return [];
        }

        // Фильтруем - оставляем только те URL, которых НЕТ в базе
        $newUrls = $this->filterExistingRecipes($allUrls);
        
        Log::info("📊 Статистика", [
            'total_found' => count($allUrls),
            'new_recipes' => count($newUrls),
            'already_in_db' => count($allUrls) - count($newUrls)
        ]);

        // Ограничиваем нужным количеством
        $result = array_slice($newUrls, 0, $targetCount);
        
        return $result;
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
}
