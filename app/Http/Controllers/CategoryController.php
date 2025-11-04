<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Показать все категории в алфавитном порядке
     */
    public function index()
    {
        // Получаем все категории, сгруппированные по первой букве
        $categories = Category::orderBy('name')->get();
        
        // Группируем по первой букве
        $categoriesByLetter = $categories->groupBy(function ($category) {
            $firstChar = mb_strtoupper(mb_substr($category->name, 0, 1));
            return $firstChar;
        });
        
        // Сортируем буквы
        $categoriesByLetter = $categoriesByLetter->sortKeys();
        
        // Разделяем на родительские и дочерние категории
        $parentCategories = $categories->where('parent_id', null);
        $childCategories = $categories->where('parent_id', '!=', null);
        
        return view('categories.index', compact('categoriesByLetter', 'parentCategories', 'childCategories'));
    }

    /**
     * Показать рецепты определенной категории
     */
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        // Получаем рецепты этой категории
        $recipes = $category->recipes()->latest()->paginate(12);
        
        // Если это AJAX-запрос для infinite scroll
        if ($request->ajax()) {
            return view('partials.recipe-cards-category', compact('recipes'))->render();
        }
        
        // Получаем дочерние категории, если есть
        $subcategories = $category->children;
        
        return view('categories.show', compact('category', 'recipes', 'subcategories'));
    }
}
