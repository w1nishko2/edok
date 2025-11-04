{{-- Компонент формы поиска --}}
<div class="search-section">
    <div class="container">
        <form action="{{ route('search') }}" method="GET" class="search-form-page">
            <div class="search-input-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       name="q" 
                       class="form-control search-input-page"
                       placeholder="Найдите свой идеальный рецепт..." 
                       value="{{ request('q') }}"
                       aria-label="Поиск рецептов"
                       autocomplete="off">
                <button type="submit" class="btn btn-search-page" aria-label="Найти">
                    Найти
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Секция поиска */
.search-section {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
    padding: 3rem 0;
    margin-bottom: 3rem;
}

.search-form-page {
    max-width: 800px;
    margin: 0 auto;
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 50px;
    padding: 0.5rem 0.75rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.search-icon {
    font-size: 1.5rem;
    color: #666;
    margin-left: 0.5rem;
    margin-right: 1rem;
}

.search-input-page {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1.1rem;
    padding: 0.75rem 0;
    background: transparent;
}

.search-input-page:focus {
    box-shadow: none;
    border: none;
}

.btn-search-page {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-search-page:hover {
    background: linear-gradient(135deg, #ff5252 0%, #ff4444 100%);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
}

.btn-search-page:active {
    transform: scale(0.98);
}

/* Адаптивность */
@media (max-width: 767.98px) {
    .search-section {
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .search-input-wrapper {
        padding: 0.4rem 0.6rem;
    }

    .search-icon {
        font-size: 1.2rem;
        margin-left: 0.25rem;
        margin-right: 0.75rem;
    }

    .search-input-page {
        font-size: 1rem;
        padding: 0.6rem 0;
    }

    .btn-search-page {
        padding: 0.6rem 1.5rem;
        font-size: 0.95rem;
    }
}

@media (max-width: 430px) {
    .search-section {
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
    }

    .search-input-wrapper {
        padding: 0.35rem 0.5rem;
        border-radius: 40px;
    }

    .search-icon {
        font-size: 1.1rem;
        margin-left: 0.25rem;
        margin-right: 0.5rem;
    }

    .search-input-page {
        font-size: 0.9rem;
        padding: 0.5rem 0;
    }

    .btn-search-page {
        padding: 0.5rem 1.2rem;
        font-size: 0.85rem;
    }
}

/* Анимация при загрузке */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-section {
    animation: fadeInUp 0.6s ease-out;
}
</style>
