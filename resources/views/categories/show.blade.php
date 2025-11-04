@extends('layouts.app')

@section('body-class', 'category-show-page')

@section('content')
{{-- Поиск рецептов --}}
@include('components.search-form')

<div class="category-show-page">
    <div class="container">
        {{-- Хлебные крошки --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Категории</a></li>
                @if($category->parent)
                    <li class="breadcrumb-item"><a href="{{ route('category.show', $category->parent->slug) }}">{{ $category->parent->name }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        {{-- Заголовок категории --}}
        <div class="category-header">
            <h1 class="category-title">{{ $category->name }}</h1>
            <p class="category-stats">
                <i class="bi bi-file-text"></i>
                Найдено рецептов: <strong>{{ $category->recipe_count }}</strong>
            </p>
        </div>

        {{-- Подкатегории --}}
        @if($subcategories->count() > 0)
            <div class="subcategories-section">
                <h2 class="section-title">Подкатегории</h2>
                <div class="subcategories-grid">
                    @foreach($subcategories as $subcategory)
                        <a href="{{ route('category.show', $subcategory->slug) }}" class="subcategory-card">
                            <i class="bi bi-folder"></i>
                            <span class="subcategory-name">{{ $subcategory->name }}</span>
                            <span class="subcategory-count">{{ $subcategory->recipe_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Рецепты --}}
        @if($recipes->count() > 0)
            <div class="recipes-section">
                <h2 class="section-title">Рецепты</h2>
                <div class="recipes-grid" id="recipes-container" data-next-page="{{ $recipes->nextPageUrl() }}">
                    @include('partials.recipe-cards-category', ['recipes' => $recipes])
                </div>

                {{-- Индикатор загрузки --}}
                <div id="loading-indicator" style="display: none;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-dark" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-3 text-muted">Загружаем еще рецепты...</p>
                    </div>
                </div>

                {{-- Сообщение о том, что больше нет контента --}}
                <div id="no-more-content" class="text-center py-4" style="display: none;">
                    <p class="text-muted">
                        <i class="bi bi-check-circle"></i>
                        Вы просмотрели все рецепты в этой категории
                    </p>
                </div>
            </div>
        @else
            <div class="no-recipes">
                <i class="bi bi-inbox"></i>
                <p>В этой категории пока нет рецептов</p>
            </div>
        @endif
    </div>
</div>

<style>
/* Основной контейнер */
.category-show-page {
    min-height: 100vh;
    background: #ffffff;
    padding: 2rem 0;
}

/* Хлебные крошки */
.breadcrumb {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.breadcrumb-item a {
    color: #333;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #000;
    text-decoration: underline;
}

/* Заголовок категории */
.category-header {
    text-align: center;
    margin-bottom: 3rem;
}

.category-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 1rem;
}

.category-stats {
    font-size: 1.1rem;
    color: #666;
}

.category-stats i {
    margin-right: 0.5rem;
}

/* Заголовки секций */
.section-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #000;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e0e0e0;
}

/* Подкатегории */
.subcategories-section {
    margin-bottom: 3rem;
}

.subcategories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.subcategory-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.subcategory-card:hover {
    background: #fff;
    border-color: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.subcategory-card i {
    font-size: 1.5rem;
    color: #666;
}

.subcategory-name {
    flex: 1;
    font-weight: 600;
    color: #000;
}

.subcategory-count {
    padding: 0.25rem 0.75rem;
    background: #000;
    color: #fff;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
}

/* Рецепты */
.recipes-section {
    margin-bottom: 3rem;
}

.recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.recipe-card {
    background: #fff;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.recipe-card:hover {
    border-color: #000;
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.recipe-link {
    display: block;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.recipe-link:hover {
    text-decoration: none;
    color: inherit;
}

.recipe-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.recipe-image-placeholder {
    width: 100%;
    height: 200px;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.recipe-image-placeholder i {
    font-size: 3rem;
    color: #ccc;
}

.recipe-content {
    padding: 1.5rem;
}

.recipe-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #000;
    margin-bottom: 0.75rem;
}

.recipe-description {
    font-size: 0.95rem;
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.recipe-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #999;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Пустое состояние */
.no-recipes {
    text-align: center;
    padding: 4rem 2rem;
    color: #999;
}

.no-recipes i {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-recipes p {
    font-size: 1.2rem;
}

/* Пагинация */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .category-title {
        font-size: 2rem;
    }
    
    .recipes-grid {
        grid-template-columns: 1fr;
    }
    
    .subcategories-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
