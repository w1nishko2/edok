<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Поиск рецептов
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $recipes = collect();
        
        if (!empty($query)) {
            // Получаем результаты поиска напрямую из БД
            $searchTerm = mb_strtolower(trim($query));
            
            $allResults = Recipe::with('categories')
                ->where(function ($q) use ($searchTerm) {
                    // Поиск по названию рецепта
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"])
                      // Поиск по описанию
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(meta_description) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(meta_keywords) LIKE ?', ["%{$searchTerm}%"])
                      // Поиск по сложности
                      ->orWhereRaw('LOWER(difficulty) LIKE ?', ["%{$searchTerm}%"])
                      // Поиск в ингредиентах (JSON)
                      ->orWhereRaw('LOWER(JSON_EXTRACT(ingredients, "$[*].name")) LIKE ?', ["%{$searchTerm}%"])
                      // Поиск в шагах приготовления (JSON)
                      ->orWhereRaw('LOWER(JSON_EXTRACT(steps, "$[*].description")) LIKE ?', ["%{$searchTerm}%"]);
                })
                // Поиск по категориям
                ->orWhereHas('categories', function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
                })
                ->get();
            
            // Фильтруем по похожести (минимум 67%)
            $recipes = $this->filterBySimilarity($allResults, $query, 0.67);
            
            // Сортируем по релевантности (похожести)
            $recipes = $recipes->sortByDesc('similarity_score')->values();
            
            // Пагинация для поиска (вручную)
            $perPage = 12;
            $total = $recipes->count();
            $recipes = $recipes->slice(($page - 1) * $perPage, $perPage)->values();
        }
        
        // Если это AJAX-запрос для infinite scroll
        if ($request->ajax()) {
            return view('partials.recipe-cards-search', compact('recipes'))->render();
        }
        
        return view('search', [
            'recipes' => $recipes,
            'query' => $query,
            'totalResults' => isset($total) ? $total : 0,
            'currentPage' => $page,
            'hasMore' => isset($total) && ($page * $perPage) < $total
        ]);
    }
    
    /**
     * Фильтрация результатов по похожести
     * 
     * @param \Illuminate\Database\Eloquent\Collection $recipes
     * @param string $query
     * @param float $minSimilarity Минимальный процент похожести (0.0 - 1.0)
     * @return \Illuminate\Support\Collection
     */
    protected function filterBySimilarity($recipes, $query, $minSimilarity = 0.67)
    {
        $query = mb_strtolower(trim($query));
        $queryWords = preg_split('/\s+/u', $query);
        
        return $recipes->map(function ($recipe) use ($query, $queryWords) {
            // Подготавливаем текст для сравнения
            $titleLower = mb_strtolower($recipe->title);
            $descriptionLower = mb_strtolower($recipe->description ?? '');
            
            // Собираем названия ингредиентов
            $ingredientsText = '';
            if (is_array($recipe->ingredients)) {
                $ingredientNames = array_map(function($ing) {
                    return mb_strtolower($ing['name'] ?? '');
                }, $recipe->ingredients);
                $ingredientsText = implode(' ', $ingredientNames);
            }
            
            // Собираем названия категорий
            $categoriesText = '';
            if ($recipe->categories && $recipe->categories->count() > 0) {
                $categoryNames = $recipe->categories->pluck('name')->map(function($name) {
                    return mb_strtolower($name);
                })->toArray();
                $categoriesText = implode(' ', $categoryNames);
            }
            
            // Весь текст для поиска
            $searchableText = implode(' ', [
                $titleLower,
                $descriptionLower,
                $ingredientsText,
                $categoriesText,
                mb_strtolower($recipe->meta_description ?? ''),
                mb_strtolower($recipe->difficulty ?? ''),
            ]);
            
            // Считаем похожесть разными методами
            $scores = [];
            
            // 1. Точное совпадение фразы в названии (высший приоритет)
            if (str_contains($titleLower, $query)) {
                $scores[] = 1.0;
            }
            
            // 2. Точное совпадение в ингредиентах
            if (str_contains($ingredientsText, $query)) {
                $scores[] = 0.95;
            }
            
            // 3. Точное совпадение в категориях
            if (str_contains($categoriesText, $query)) {
                $scores[] = 0.90;
            }
            
            // 4. Точное совпадение в описании
            if (str_contains($descriptionLower, $query)) {
                $scores[] = 0.85;
            }
            
            // 5. Совпадение по словам
            $matchedWords = 0;
            $totalWords = count($queryWords);
            
            foreach ($queryWords as $word) {
                if (mb_strlen($word) >= 2) { // Учитываем даже короткие слова
                    if (str_contains($titleLower, $word)) {
                        $matchedWords += 2; // Больший вес для совпадения в названии
                    } elseif (str_contains($ingredientsText, $word)) {
                        $matchedWords += 1.5; // Средний вес для ингредиентов
                    } elseif (str_contains($categoriesText, $word)) {
                        $matchedWords += 1.3; // Вес для категорий
                    } elseif (str_contains($searchableText, $word)) {
                        $matchedWords += 1; // Базовый вес
                    }
                }
            }
            
            if ($totalWords > 0) {
                // Нормализуем с учетом весов (максимум 2 за слово)
                $wordMatchScore = min(1.0, $matchedWords / ($totalWords * 2));
                $scores[] = $wordMatchScore;
            }
            
            // 6. similar_text для названия
            similar_text($query, $titleLower, $percent);
            if ($percent > 0) {
                $scores[] = $percent / 100;
            }
            
            // 7. Частичное совпадение (для случаев типа "яичниц" vs "яичница")
            $partialMatch = false;
            foreach ($queryWords as $word) {
                if (mb_strlen($word) >= 3) {
                    $pattern = preg_quote($word, '/');
                    if (preg_match("/{$pattern}/ui", $titleLower) || 
                        preg_match("/{$pattern}/ui", $ingredientsText)) {
                        $partialMatch = true;
                        break;
                    }
                }
            }
            if ($partialMatch) {
                $scores[] = 0.75;
            }
            
            // Усредняем все оценки
            $finalScore = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
            
            // Добавляем оценку в объект рецепта
            $recipe->similarity_score = round($finalScore * 100, 2);
            
            return $recipe;
        })->filter(function ($recipe) use ($minSimilarity) {
            // Фильтруем по минимальной похожести (67%)
            return ($recipe->similarity_score / 100) >= $minSimilarity;
        });
    }
}
