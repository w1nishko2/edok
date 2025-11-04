@extends('layouts.app')

@section('body-class', 'search-page')

@section('content')
<div class="search-page">
    <div class="container py-4">
        <!-- Поисковая строка -->
        <div class="search-header mb-4">
            <h1 class="search-title">
                <i class="bi bi-search"></i> Поиск рецептов
            </h1>
            
            <form action="{{ route('search') }}" method="GET" class="search-form-large">
                <div class="input-group">
                    <input 
                        type="text" 
                        name="q" 
                        class="form-control search-input" 
                        placeholder="Введите название рецепта, ингредиент или блюдо..." 
                        value="{{ $query }}"
                        autofocus
                    >
                    <button type="submit" class="btn btn-search">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Результаты поиска -->
        @if($query)
            <div class="search-results">
                <div class="results-info mb-4">
                    <h2 class="results-count">
                        @if($totalResults > 0)
                            Найдено {{ $totalResults }} {{ $totalResults == 1 ? 'рецепт' : ($totalResults < 5 ? 'рецепта' : 'рецептов') }}
                            @if($query)
                                по запросу "<span class="query-text">{{ $query }}</span>"
                            @endif
                        @else
                            Ничего не найдено по запросу "<span class="query-text">{{ $query }}</span>"
                        @endif
                    </h2>
                    
                    @if($totalResults > 0)
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i> 
                            Показаны рецепты с похожестью от 67%
                        </p>
                    @endif
                </div>

                @if($totalResults > 0)
                    <div class="row g-4" id="recipes-container" 
                         data-next-page="{{ isset($hasMore) && $hasMore ? route('search', ['q' => $query, 'page' => ($currentPage ?? 1) + 1]) : '' }}">
                        @include('partials.recipe-cards-search', ['recipes' => $recipes])
                    </div>

                    {{-- Индикатор загрузки --}}
                    <div id="loading-indicator" style="display: none;">
                        <div class="text-center py-5">
                            <div class="spinner-border text-dark" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                            <p class="mt-3 text-muted">Загружаем еще результаты...</p>
                        </div>
                    </div>

                    {{-- Сообщение о том, что больше нет контента --}}
                    <div id="no-more-content" class="text-center py-4" style="display: none;">
                        <p class="text-muted">
                            <i class="bi bi-check-circle"></i>
                            Это все результаты по вашему запросу
                        </p>
                    </div>
                @else
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3>Ничего не найдено</h3>
                        <p class="text-muted">
                            Попробуйте изменить запрос или воспользуйтесь другими ключевыми словами
                        </p>
                        
                        <div class="search-suggestions mt-4">
                            <h5>Попробуйте поискать:</h5>
                            <div class="suggestion-tags">
                                <a href="{{ route('search') }}?q=курица" class="suggestion-tag">Курица</a>
                                <a href="{{ route('search') }}?q=салат" class="suggestion-tag">Салат</a>
                                <a href="{{ route('search') }}?q=десерт" class="suggestion-tag">Десерт</a>
                                <a href="{{ route('search') }}?q=суп" class="suggestion-tag">Суп</a>
                                <a href="{{ route('search') }}?q=выпечка" class="suggestion-tag">Выпечка</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="search-empty-state">
                <div class="empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>Введите запрос для поиска</h3>
                <p class="text-muted">
                    Найдите рецепты по названию, ингредиентам или типу блюда
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
