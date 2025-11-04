@extends('layouts.app')

@section('body-class', 'recipe-page')

@section('content')
<article class="recipe-page" itemscope itemtype="https://schema.org/Recipe">
    <!-- Container для всего контента -->
    <div class="recipe-container">
        
        <!-- Левая колонка с контентом -->
        <div class="recipe-main-content">
            
            <!-- Breadcrumbs для навигации и SEO -->
            <nav aria-label="breadcrumb" class="recipe-breadcrumbs">
                <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ route('home') }}" itemprop="item">
                            <span itemprop="name">Главная</span>
                        </a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span itemprop="name">{{ $recipe->title }}</span>
                        <meta itemprop="position" content="2">
                    </li>
                </ol>
            </nav>

            <!-- Шапка с кнопкой назад и заголовком -->
            <header class="recipe-header">
                <a href="{{ route('home') }}" class="back-btn" aria-label="Вернуться к списку рецептов">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="recipe-title" itemprop="name">{{ $recipe->title }}</h1>
            </header>

    <!-- Мета-информация для Schema.org -->
    <meta itemprop="datePublished" content="{{ $recipe->created_at->toIso8601String() }}">
    <meta itemprop="dateModified" content="{{ $recipe->updated_at->toIso8601String() }}">
    
    @if($recipe->prep_time)
        <meta itemprop="prepTime" content="PT{{ $recipe->prep_time }}M">
    @endif
    @if($recipe->cook_time)
        <meta itemprop="cookTime" content="PT{{ $recipe->cook_time }}M">
    @endif
    @if($recipe->total_time)
        <meta itemprop="totalTime" content="PT{{ $recipe->total_time }}M">
    @endif
    @if($recipe->servings)
        <meta itemprop="recipeYield" content="{{ $recipe->servings }} порций">
    @endif

    <!-- Изображение рецепта -->
    @if($recipe->image_path)
        <div class="recipe-image-container">
            <img src="{{ Storage::url($recipe->image_path) }}" 
                 class="recipe-image" 
                 alt="{{ $recipe->title }}"
                 itemprop="image"
                 width="1200"
                 height="800"
                 loading="eager">
        </div>
    @endif

    <!-- Контент рецепта -->
    <div class="recipe-content" itemscope>
        
        <!-- Время приготовления и порции -->
        @if($recipe->prep_time || $recipe->cook_time || $recipe->servings || $recipe->difficulty)
            <div class="recipe-info-row mb-4">
                @if($recipe->prep_time)
                    <div class="info-item">
                        <i class="bi bi-clock"></i>
                        <span>Подготовка: {{ $recipe->prep_time }} мин</span>
                    </div>
                @endif
                @if($recipe->cook_time)
                    <div class="info-item">
                        <i class="bi bi-stopwatch"></i>
                        <span>Приготовление: {{ $recipe->cook_time }} мин</span>
                    </div>
                @endif
                @if($recipe->servings)
                    <div class="info-item servings-calculator">
                        <i class="bi bi-people"></i>
                        <span>Порций:</span>
                        <div class="servings-controls">
                            <button type="button" class="servings-btn" id="decreaseServings" title="Уменьшить количество порций">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="servings-value" id="servingsValue">{{ $recipe->servings }}</span>
                            <button type="button" class="servings-btn" id="increaseServings" title="Увеличить количество порций">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <input type="hidden" id="originalServings" value="{{ $recipe->servings }}">
                        <small class="servings-hint">
                            <i class="bi bi-calculator"></i>
                            Авто-пересчет
                        </small>
                    </div>
                @endif
                @if($recipe->difficulty)
                    <div class="info-item">
                        <i class="bi bi-speedometer2"></i>
                        <span>
                            @if($recipe->difficulty === 'easy') Легко
                            @elseif($recipe->difficulty === 'medium') Средне
                            @else Сложно
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Статистика -->
        <div class="recipe-stats-row">
            @if($recipe->views)
                <div class="stat-item">
                    <i class="bi bi-eye"></i>
                    <span>{{ number_format($recipe->views, 0, ',', ' ') }}</span>
                </div>
            @endif
            @if($recipe->likes)
                <div class="stat-item">
                    <i class="bi bi-hand-thumbs-up"></i>
                    <span>{{ $recipe->likes }}</span>
                </div>
            @endif
            @if($recipe->dislikes)
                <div class="stat-item">
                    <i class="bi bi-hand-thumbs-down"></i>
                    <span>{{ $recipe->dislikes }}</span>
                </div>
            @endif
            @if($recipe->rating > 0)
                <div class="stat-item" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>
                        <span itemprop="ratingValue">{{ number_format($recipe->rating, 1) }}</span>
                        <meta itemprop="ratingCount" content="{{ $recipe->rating_count }}">
                        <meta itemprop="bestRating" content="5">
                        <meta itemprop="worstRating" content="1">
                    </span>
                </div>
            @endif
        </div>

        <!-- Описание -->
        @if($recipe->description)
            <section class="description-section">
                <p class="description-text" itemprop="description">{{ $recipe->description }}</p>
            </section>
        @endif

        <!-- Пищевая ценность -->
        @if($recipe->nutrition)
            <section class="nutrition-card" itemprop="nutrition" itemscope itemtype="https://schema.org/NutritionInformation">
                <h2 class="section-title">
                    Пищевая ценность 
                    <span class="nutrition-servings-info">
                        (на <span id="nutritionServingsDisplay">{{ $recipe->servings ?? 1 }}</span> 
                        <span id="nutritionServingsText">
                            @if(($recipe->servings ?? 1) == 1)
                                порцию
                            @elseif(($recipe->servings ?? 1) >= 2 && ($recipe->servings ?? 1) <= 4)
                                порции
                            @else
                                порций
                            @endif
                        </span>)
                    </span>
                </h2>
                <div class="nutrition-grid">
                    @php
                        // Рассчитываем калории: белки×4 + жиры×9 + углеводы×4
                        $proteins = isset($recipe->nutrition['proteins']) ? floatval($recipe->nutrition['proteins']) : 0;
                        $fats = isset($recipe->nutrition['fats']) ? floatval($recipe->nutrition['fats']) : 0;
                        $carbs = isset($recipe->nutrition['carbs']) ? floatval($recipe->nutrition['carbs']) : 0;
                        $calculatedCalories = round($proteins * 4 + $fats * 9 + $carbs * 4);
                    @endphp
                    
                    @if($calculatedCalories > 0 || isset($recipe->nutrition['proteins']) || isset($recipe->nutrition['fats']) || isset($recipe->nutrition['carbs']))
                        <div class="nutrition-item">
                            <div class="nutrition-label">Калории</div>
                            <div class="nutrition-value" itemprop="calories">
                                <span class="nutrition-calc-value" data-original="0" data-type="calories">
                                    {{ $calculatedCalories }}
                                </span> ккал
                            </div>
                        </div>
                    @endif
                    @if(isset($recipe->nutrition['proteins']))
                        <div class="nutrition-item">
                            <div class="nutrition-label">Белки</div>
                            <div class="nutrition-value">
                                <span class="nutrition-calc-value" data-original="{{ $recipe->nutrition['proteins'] }}" data-type="proteins">
                                    {{ $recipe->nutrition['proteins'] }}
                                </span> г
                                <meta itemprop="proteinContent" content="{{ $recipe->nutrition['proteins'] }} г">
                            </div>
                        </div>
                    @endif
                    @if(isset($recipe->nutrition['fats']))
                        <div class="nutrition-item">
                            <div class="nutrition-label">Жиры</div>
                            <div class="nutrition-value">
                                <span class="nutrition-calc-value" data-original="{{ $recipe->nutrition['fats'] }}" data-type="fats">
                                    {{ $recipe->nutrition['fats'] }}
                                </span> г
                                <meta itemprop="fatContent" content="{{ $recipe->nutrition['fats'] }} г">
                            </div>
                        </div>
                    @endif
                    @if(isset($recipe->nutrition['carbs']))
                        <div class="nutrition-item">
                            <div class="nutrition-label">Углеводы</div>
                            <div class="nutrition-value">
                                <span class="nutrition-calc-value" data-original="{{ $recipe->nutrition['carbs'] }}" data-type="carbs">
                                    {{ $recipe->nutrition['carbs'] }}
                                </span> г
                                <meta itemprop="carbohydrateContent" content="{{ $recipe->nutrition['carbs'] }} г">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="nutrition-note">
                    <i class="bi bi-info-circle"></i>
                    <small>Калории рассчитываются автоматически по формуле: <strong>белки×4 + жиры×9 + углеводы×4</strong>. Все значения пересчитываются при изменении количества порций.</small>
                </div>
            </section>
        @endif

        <!-- Ингредиенты -->
        @if($recipe->ingredients && count($recipe->ingredients) > 0)
            <section class="ingredients-section">
                <div class="section-header-with-action">
                    <h2 class="section-title">Ингредиенты (<span id="ingredientsCount">{{ count($recipe->ingredients) }}</span>)</h2>
                    <button class="reset-ingredients-btn" id="resetIngredientsBtn" style="display: none;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Сбросить
                    </button>
                </div>
                <ul class="ingredients-list">
                    @foreach($recipe->ingredients as $index => $ingredient)
                        <li class="ingredient-item">
                            <label class="ingredient-checkbox-container">
                                <input type="checkbox" class="ingredient-checkbox" id="ingredient-{{ $index }}">
                                <span class="custom-checkbox">
                                    <i class="bi bi-check"></i>
                                </span>
                                <span class="ingredient-text" itemprop="recipeIngredient">
                                    @if(isset($ingredient['name']))
                                        {{ $ingredient['name'] }}
                                    @else
                                        {{ $ingredient }}
                                    @endif
                                    @if(isset($ingredient['quantity']))
                                        <span class="ingredient-quantity ingredient-calc-quantity" 
                                              data-original="{{ $ingredient['quantity'] }}">
                                            {{ $ingredient['quantity'] }}
                                        </span>
                                    @endif
                                    @if(isset($ingredient['measure']))
                                        <span class="ingredient-measure">{{ $ingredient['measure'] }}</span>
                                    @endif
                                </span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <!-- Шаги приготовления -->
        @if($recipe->steps && count($recipe->steps) > 0)
            <section class="steps-section">
                <h2 class="section-title">Приготовление ({{ count($recipe->steps) }} шагов)</h2>
                <ol class="steps-list" itemprop="recipeInstructions">
                    @foreach($recipe->steps as $index => $step)
                        <li class="step-item" itemprop="step" itemscope itemtype="https://schema.org/HowToStep">
                            <meta itemprop="position" content="{{ $index + 1 }}">
                            
                            <div class="step-header">
                                <div class="step-number">{{ $index + 1 }}</div>
                            </div>
                            
                            @if(is_array($step) && isset($step['image']))
                                <div class="step-image-container">
                                    <img src="{{ $step['image'] }}" 
                                         alt="Шаг {{ $index + 1 }}" 
                                         class="step-image"
                                         itemprop="image"
                                         loading="lazy">
                                </div>
                            @endif
                            
                            <div class="step-content" itemprop="text">
                                @if(is_array($step))
                                    {{ $step['description'] ?? $step['text'] ?? '' }}
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <!-- Автор рецепта (для Schema.org) -->
        <meta itemprop="author" content="{{ config('app.name') }}">

        <!-- Кнопка "Назад к рецептам" -->
        <div class="back-to-home">
            <a href="{{ route('home') }}" class="back-home-btn">
                <i class="bi bi-grid-3x3-gap"></i>
                Все рецепты
            </a>
        </div>
        
        </div><!-- Закрываем recipe-content -->
        
        </div><!-- Закрываем recipe-main-content -->
        
        <!-- Правая колонка для рекламы (видна только на десктопе) -->
        <aside class="recipe-sidebar">
            
            <!-- Sticky контейнер для рекламы -->
            <div class="sidebar-sticky">
                
                <!-- Рекламный блок 1 -->
                <div class="ad-block ad-block-1">
                    <div class="ad-placeholder">
                        <i class="bi bi-badge-ad"></i>
                        <span>Реклама 300x250</span>
                    </div>
                    <!-- Здесь будет рекламный код, например Google AdSense -->
                    {{-- 
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-XXXXXXXXXX"
                         data-ad-slot="XXXXXXXXXX"
                         data-ad-format="auto"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                    --}}
                </div>
                
                <!-- Рекламный блок 2 -->
                <div class="ad-block ad-block-2">
                    <div class="ad-placeholder">
                        <i class="bi bi-badge-ad"></i>
                        <span>Реклама 300x600</span>
                    </div>
                </div>
                
                <!-- Популярные рецепты (опционально) -->
                <div class="popular-recipes">
                    <h3>Популярное</h3>
                    <p class="text-muted small">Здесь могут быть популярные рецепты</p>
                </div>
                
            </div>
            
        </aside>
        
    </div><!-- Закрываем recipe-container -->
    
</article>

<script>
// Калькулятор порций
document.addEventListener('DOMContentLoaded', function() {
    const increaseBtn = document.getElementById('increaseServings');
    const decreaseBtn = document.getElementById('decreaseServings');
    const servingsValue = document.getElementById('servingsValue');
    const originalServingsInput = document.getElementById('originalServings');
    
    if (!increaseBtn || !decreaseBtn || !servingsValue || !originalServingsInput) {
        console.log('Калькулятор порций не инициализирован - элементы не найдены');
        return;
    }
    
    const originalServings = parseFloat(originalServingsInput.value);
    let currentServings = originalServings;
    
    // Функция для обновления всех значений
    function updateValues(newServings) {
        const multiplier = newServings / originalServings;
        currentServings = newServings;
        
        // Обновляем отображение количества порций
        servingsValue.textContent = newServings;
        
        // Обновляем текст в заголовке пищевой ценности
        const nutritionServingsDisplay = document.getElementById('nutritionServingsDisplay');
        const nutritionServingsText = document.getElementById('nutritionServingsText');
        if (nutritionServingsDisplay) {
            nutritionServingsDisplay.textContent = newServings;
        }
        if (nutritionServingsText) {
            if (newServings == 1) {
                nutritionServingsText.textContent = 'порцию';
            } else if (newServings >= 2 && newServings <= 4) {
                nutritionServingsText.textContent = 'порции';
            } else {
                nutritionServingsText.textContent = 'порций';
            }
        }
        
        // Обновляем количество ингредиентов
        const ingredientQuantities = document.querySelectorAll('.ingredient-calc-quantity');
        ingredientQuantities.forEach(element => {
            const originalText = element.dataset.original;
            
            // Пытаемся распарсить число из текста
            const numberMatch = originalText.match(/[\d.,]+/);
            if (numberMatch) {
                const original = parseFloat(numberMatch[0].replace(',', '.'));
                if (!isNaN(original)) {
                    const newValue = original * multiplier;
                    
                    // Форматируем число красиво
                    let formattedValue;
                    if (newValue < 1) {
                        formattedValue = newValue.toFixed(2).replace(/\.?0+$/, '');
                    } else if (newValue < 10) {
                        formattedValue = newValue.toFixed(1).replace(/\.0$/, '');
                    } else {
                        formattedValue = Math.round(newValue);
                    }
                    
                    // Заменяем число в оригинальном тексте
                    element.textContent = originalText.replace(/[\d.,]+/, formattedValue);
                    
                    // Добавляем анимацию
                    element.classList.add('updating');
                    setTimeout(() => element.classList.remove('updating'), 300);
                }
            }
        });
        
        // Обновляем пищевую ценность (умножаем на количество порций)
        const nutritionValues = document.querySelectorAll('.nutrition-calc-value');
        let proteins = 0, fats = 0, carbs = 0;
        
        nutritionValues.forEach(element => {
            const original = parseFloat(element.dataset.original);
            const type = element.dataset.type;
            
            if (!isNaN(original) && original > 0) {
                // Пищевая ценность умножается на количество порций
                const totalValue = original * newServings;
                const roundedValue = Math.round(totalValue);
                
                // Сохраняем значения для расчета калорий
                if (type === 'proteins') proteins = roundedValue;
                if (type === 'fats') fats = roundedValue;
                if (type === 'carbs') carbs = roundedValue;
                
                // Обновляем отображение (кроме калорий - их рассчитаем отдельно)
                if (type !== 'calories') {
                    element.textContent = roundedValue;
                    element.classList.add('updating');
                    setTimeout(() => element.classList.remove('updating'), 300);
                }
            }
        });
        
        // Рассчитываем и обновляем калории: белки×4 + жиры×9 + углеводы×4
        const caloriesElement = document.querySelector('.nutrition-calc-value[data-type="calories"]');
        if (caloriesElement) {
            const calculatedCalories = Math.round(proteins * 4 + fats * 9 + carbs * 4);
            caloriesElement.textContent = calculatedCalories;
            caloriesElement.classList.add('updating');
            setTimeout(() => caloriesElement.classList.remove('updating'), 300);
        }
        
        // Обновляем состояние кнопок
        decreaseBtn.disabled = currentServings <= 1;
        increaseBtn.disabled = currentServings >= 99;
    }
    
    // Обработчики кнопок
    increaseBtn.addEventListener('click', function() {
        if (currentServings < 99) {
            updateValues(currentServings + 1);
        }
    });
    
    decreaseBtn.addEventListener('click', function() {
        if (currentServings > 1) {
            updateValues(currentServings - 1);
        }
    });
    
    // Инициализация: рассчитываем калории для исходного количества порций
    const nutritionValues = document.querySelectorAll('.nutrition-calc-value');
    let initialProteins = 0, initialFats = 0, initialCarbs = 0;
    
    nutritionValues.forEach(element => {
        const original = parseFloat(element.dataset.original);
        const type = element.dataset.type;
        
        if (!isNaN(original) && original > 0) {
            if (type === 'proteins') initialProteins = original;
            if (type === 'fats') initialFats = original;
            if (type === 'carbs') initialCarbs = original;
        }
    });
    
    // Устанавливаем начальные калории
    const caloriesElement = document.querySelector('.nutrition-calc-value[data-type="calories"]');
    if (caloriesElement && (initialProteins > 0 || initialFats > 0 || initialCarbs > 0)) {
        const initialCalories = Math.round(initialProteins * 4 + initialFats * 9 + initialCarbs * 4);
        caloriesElement.textContent = initialCalories;
    }
    
    // Инициализация состояния кнопок
    updateValues(currentServings);
});

// Сохранение и восстановление состояния чекбоксов ингредиентов
document.addEventListener('DOMContentLoaded', function() {
    const recipeSlug = '{{ $recipe->slug }}';
    const storageKey = `recipe-ingredients-${recipeSlug}`;
    
    // Получаем все чекбоксы ингредиентов
    const checkboxes = document.querySelectorAll('.ingredient-checkbox');
    const resetBtn = document.getElementById('resetIngredientsBtn');
    
    // Функция для обновления видимости кнопки сброса
    function updateResetButtonVisibility() {
        const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (resetBtn) {
            resetBtn.style.display = hasChecked ? 'inline-flex' : 'none';
        }
    }
    
    // Восстанавливаем сохраненное состояние
    const savedState = localStorage.getItem(storageKey);
    if (savedState) {
        try {
            const checkedIndices = JSON.parse(savedState);
            checkboxes.forEach((checkbox, index) => {
                if (checkedIndices.includes(index)) {
                    checkbox.checked = true;
                }
            });
        } catch (e) {
            console.error('Ошибка при восстановлении состояния ингредиентов:', e);
        }
    }
    
    // Обновляем видимость кнопки при загрузке
    updateResetButtonVisibility();
    
    // Сохраняем состояние при изменении
    checkboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            // Собираем индексы отмеченных чекбоксов
            const checkedIndices = [];
            checkboxes.forEach((cb, idx) => {
                if (cb.checked) {
                    checkedIndices.push(idx);
                }
            });
            
            // Сохраняем в localStorage
            localStorage.setItem(storageKey, JSON.stringify(checkedIndices));
            
            // Обновляем видимость кнопки
            updateResetButtonVisibility();
        });
    });
    
    // Обработчик кнопки сброса
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Снимаем все галочки
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Удаляем из localStorage
            localStorage.removeItem(storageKey);
            
            // Скрываем кнопку
            updateResetButtonVisibility();
        });
    }
});
</script>
@endsection
