<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\SeoService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $recipes = Recipe::latest()->paginate(12);
        $totalRecipes = Recipe::count();
        
        // Если это AJAX-запрос для infinite scroll
        if ($request->ajax()) {
            return view('partials.recipe-cards', compact('recipes'))->render();
        }
        
        // Генерируем SEO-данные
        $seo = $this->seoService->generateHomeSeo($totalRecipes);
        $itemListSchema = $this->seoService->generateItemListSchema($recipes);
        
        return view('home', compact('recipes', 'totalRecipes', 'seo', 'itemListSchema'));
    }

    /**
     * Show recipe details for clients.
     *
     * @param string $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($slug)
    {
        $recipe = Recipe::where('slug', $slug)->firstOrFail();
        
        // Увеличиваем счетчик просмотров
        $recipe->increment('views');
        
        // Генерируем SEO-данные
        $seo = $this->seoService->generateRecipeSeo($recipe);
        
        return view('recipe-show', compact('recipe', 'seo'));
    }
}
