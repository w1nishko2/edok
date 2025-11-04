@extends('layouts.app')

@section('body-class', 'categories-page')

@section('content')
{{-- Поиск рецептов --}}
@include('components.search-form')

<div class="categories-page">
    <div class="container">
        <h1 class="page-title">Каталог рецептов по категориям</h1>
        <p class="page-subtitle">Все категории в алфавитном порядке</p>

        {{-- Алфавитная навигация --}}
        <div class="alphabet-nav">
            @foreach($categoriesByLetter->keys() as $letter)
                <a href="#letter-{{ $letter }}" class="alphabet-link">{{ $letter }}</a>
            @endforeach
        </div>

        {{-- Список категорий по буквам --}}
        <div class="categories-list">
            @foreach($categoriesByLetter as $letter => $letterCategories)
                <div class="letter-section" id="letter-{{ $letter }}">
                    <h2 class="letter-header">{{ $letter }}</h2>
                    
                    <div class="categories-grid">
                        @foreach($letterCategories as $category)
                            <div class="category-card">
                                <a href="{{ route('category.show', $category->slug) }}" class="category-link">
                                    <div class="category-header">
                                        @if($category->isParent())
                                            <i class="bi bi-folder-fill"></i>
                                        @else
                                            <i class="bi bi-folder"></i>
                                        @endif
                                        <h3 class="category-name">{{ $category->name }}</h3>
                                    </div>
                                    
                                    <div class="category-info">
                                        <span class="recipe-count">
                                            <i class="bi bi-file-text"></i>
                                            {{ $category->recipe_count }} {{ trans_choice('рецепт|рецепта|рецептов', $category->recipe_count) }}
                                        </span>
                                        
                                        @if($category->parent)
                                            <span class="parent-category">
                                                <i class="bi bi-arrow-return-right"></i>
                                                {{ $category->parent->name }}
                                            </span>
                                        @endif
                                        
                                        @if($category->children->count() > 0)
                                            <span class="subcategories-count">
                                                <i class="bi bi-collection"></i>
                                                {{ $category->children->count() }} подкатегорий
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* Основной контейнер */
.categories-page {
    min-height: 100vh;
    background: #ffffff;
    padding: 2rem 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 0.5rem;
    text-align: center;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #666;
    text-align: center;
    margin-bottom: 2rem;
}

/* Алфавитная навигация */
.alphabet-nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 3rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.alphabet-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 50%;
    color: #333;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.alphabet-link:hover {
    background: #000;
    color: #fff;
    border-color: #000;
    transform: scale(1.1);
}

/* Секции по буквам */
.letter-section {
    margin-bottom: 3rem;
}

.letter-header {
    font-size: 3rem;
    font-weight: 700;
    color: #000;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #000;
}

/* Сетка категорий */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

/* Карточка категории */
.category-card {
    background: #fff;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.category-card:hover {
    border-color: #000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.category-link {
    display: block;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.category-header i {
    font-size: 1.5rem;
    color: #666;
}

.category-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #000;
    margin: 0;
}

/* Информация о категории */
.category-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.category-info span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-info i {
    font-size: 1rem;
}

.recipe-count {
    font-weight: 600;
    color: #333;
}

.parent-category {
    color: #999;
    font-style: italic;
}

.subcategories-count {
    color: #666;
}

/* Адаптивность */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .alphabet-nav {
        gap: 0.25rem;
    }
    
    .alphabet-link {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .letter-header {
        font-size: 2rem;
    }
}
</style>
@endsection
