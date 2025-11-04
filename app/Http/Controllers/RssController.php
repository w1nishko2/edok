<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Response;

class RssController extends Controller
{
    /**
     * Генерация RSS фида для новых рецептов
     */
    public function recipes(): Response
    {
        $recipes = Recipe::latest()
            ->take(50)
            ->get();

        $rss = view('rss.recipes', [
            'recipes' => $recipes,
            'buildDate' => now(),
        ])->render();

        return response($rss, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /**
     * Генерация Atom фида для новых рецептов
     */
    public function atom(): Response
    {
        $recipes = Recipe::latest()
            ->take(50)
            ->get();

        $atom = view('rss.atom', [
            'recipes' => $recipes,
            'updated' => now(),
        ])->render();

        return response($atom, 200)
            ->header('Content-Type', 'application/atom+xml; charset=utf-8');
    }

    /**
     * Яндекс.Новости RSS (для новостных агрегаторов)
     */
    public function yandexNews(): Response
    {
        $recipes = Recipe::latest()
            ->where('created_at', '>=', now()->subDays(7))
            ->take(50)
            ->get();

        $rss = view('rss.yandex-news', [
            'recipes' => $recipes,
            'buildDate' => now(),
        ])->render();

        return response($rss, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /**
     * Яндекс.Дзен RSS
     */
    public function yandexZen(): Response
    {
        $recipes = Recipe::latest()
            ->take(100)
            ->get();

        $rss = view('rss.yandex-zen', [
            'recipes' => $recipes,
            'buildDate' => now(),
        ])->render();

        return response($rss, 200)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
