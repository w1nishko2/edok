<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\RecipeListParserService;
use App\Services\RecipeParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Главная страница парсера
     */
    public function index()
    {
        $recipesCount = Recipe::count();
        $latestRecipes = Recipe::latest()->take(10)->get();

        return view('admin.parser.index', compact('recipesCount', 'latestRecipes'));
    }

    /**
     * Запуск парсера
     */
    public function startParsing(Request $request)
    {
        $request->validate([
            'pages' => 'required|integer|min:1|max:10',
            'limit' => 'required|integer|min:1|max:100',
        ]);

        try {
            $pages = $request->input('pages', 1);
            $limit = $request->input('limit', 10);

            // Получаем список URL рецептов
            $listParser = new RecipeListParserService();
            $recipeUrls = $listParser->parseMultiplePages($pages);

            if (empty($recipeUrls)) {
                return back()->with('error', 'Не найдено ни одного рецепта!');
            }

            // Ограничиваем количество
            $recipeUrls = array_slice($recipeUrls, 0, $limit);

            // Парсим каждый рецепт
            $recipeParser = new RecipeParserService();
            $successful = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($recipeUrls as $url) {
                try {
                    $recipe = $recipeParser->parseRecipe($url);
                    
                    if ($recipe) {
                        $successful++;
                    } else {
                        $skipped++;
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Ошибка парсинга {$url}: " . $e->getMessage());
                }

                // Небольшая задержка
                sleep(2);
            }

            $message = "Парсинг завершен! Добавлено: {$successful}, Пропущено: {$skipped}, Ошибок: {$errors}";
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error("Ошибка запуска парсера: " . $e->getMessage());
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Просмотр всех рецептов
     */
    public function recipes()
    {
        $recipes = Recipe::latest()->paginate(20);
        return view('admin.parser.recipes', compact('recipes'));
    }

    /**
     * Просмотр отдельного рецепта
     */
    public function show($id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('admin.parser.show', compact('recipe'));
    }

    /**
     * Удаление рецепта
     */
    public function destroy($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);
            
            // Удаляем изображение если есть
            if ($recipe->image_path) {
                Storage::disk('public')->delete($recipe->image_path);
            }
            
            $recipe->delete();
            
            return back()->with('success', 'Рецепт успешно удален!');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка удаления: ' . $e->getMessage());
        }
    }
}
