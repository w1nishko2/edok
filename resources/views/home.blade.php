@extends('layouts.app')

@section('body-class', 'home-page')

@section('content')
{{-- Поиск рецептов --}}
@include('components.search-form')

<div class="custom-page" itemscope itemtype="https://schema.org/WebPage">
    <div class="container">
        <header class="text-center mb-5">
            <h1 class="custom-title" itemprop="headline">Рецепты с фото пошагово - Простые и вкусные блюда</h1>
            <p class="custom-subtitle" itemprop="description">
                Более {{ number_format($totalRecipes, 0, ',', ' ') }} проверенных рецептов различных кухонь мира с подробными фото-инструкциями
            </p>
        </header>

        @if($recipes->count() > 0)
            <div class="row g-4" id="recipes-container" data-next-page="{{ $recipes->nextPageUrl() }}">
                @include('partials.recipe-cards', ['recipes' => $recipes])
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

            {{-- Сообщение о том, что больше нет контента (скрыто по умолчанию) --}}
            <div id="no-more-content" class="text-center py-4" style="display: none;">
                <p class="text-muted">
                    <i class="bi bi-check-circle"></i>
                    Вы просмотрели все доступные рецепты
                </p>
            </div>
        @else
            <div class="custom-alert">
                <h4>Рецепты не найдены</h4>
                <p class="mb-0">Запустите парсер в <a href="{{ route('admin.parser.index') }}" class="custom-link">панели администратора</a></p>
            </div>
        @endif
    </div>
</div>
@endsection
