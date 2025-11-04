<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/recipe/{slug}', [App\Http\Controllers\HomeController::class, 'show'])->name('recipe.show');

// Search route
Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');

// Categories routes
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{slug}', [App\Http\Controllers\CategoryController::class, 'show'])->name('category.show');

// SEO routes
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

// Policy & Contact routes
Route::get('/privacy-policy', [App\Http\Controllers\PolicyController::class, 'privacy'])->name('privacy.policy');
Route::get('/terms', [App\Http\Controllers\PolicyController::class, 'terms'])->name('terms');
Route::get('/contact', [App\Http\Controllers\PolicyController::class, 'contact'])->name('contact');

// RSS/Atom feeds (новое для 2025)
Route::get('/rss', [App\Http\Controllers\RssController::class, 'recipes'])->name('rss.recipes');
Route::get('/feed', [App\Http\Controllers\RssController::class, 'recipes'])->name('rss.feed');
Route::get('/atom', [App\Http\Controllers\RssController::class, 'atom'])->name('rss.atom');
Route::get('/yandex-zen.xml', [App\Http\Controllers\RssController::class, 'yandexZen'])->name('rss.yandex-zen');
Route::get('/yandex-news.xml', [App\Http\Controllers\RssController::class, 'yandexNews'])->name('rss.yandex-news');

if (app()->environment('production')) {
    URL::forceScheme('https');
}
Auth::routes();

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
    
    // Parser routes
    Route::prefix('parser')->name('parser.')->group(function () {
        Route::get('/', [App\Http\Controllers\ParserController::class, 'index'])->name('index');
        Route::post('/start', [App\Http\Controllers\ParserController::class, 'startParsing'])->name('start');
        Route::get('/recipes', [App\Http\Controllers\ParserController::class, 'recipes'])->name('recipes');
        Route::get('/recipes/{id}', [App\Http\Controllers\ParserController::class, 'show'])->name('show');
        Route::delete('/recipes/{id}', [App\Http\Controllers\ParserController::class, 'destroy'])->name('destroy');
    });
});
