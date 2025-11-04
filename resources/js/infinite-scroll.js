/**
 * Infinite Scroll Component
 * Автоматически загружает контент при прокрутке до конца страницы
 */

export default class InfiniteScroll {
    constructor(options = {}) {
        this.container = options.container || '#recipes-container';
        this.loader = options.loader || '#loading-indicator';
        this.nextPageUrl = options.nextPageUrl || null;
        this.isLoading = false;
        this.hasMore = true;
        this.threshold = options.threshold || 300; // Расстояние от низа страницы для начала загрузки (в пикселях)
        
        this.init();
    }
    
    init() {
        // Проверяем, что контейнер существует
        const container = document.querySelector(this.container);
        if (!container) {
            console.warn('InfiniteScroll: Container not found');
            return;
        }
        
        // Получаем URL следующей страницы из data-атрибута
        this.nextPageUrl = container.dataset.nextPage;
        
        // Принудительно заменяем HTTP на HTTPS для безопасности
        if (this.nextPageUrl && this.nextPageUrl.startsWith('http://')) {
            this.nextPageUrl = this.nextPageUrl.replace('http://', 'https://');
        }
        
        if (!this.nextPageUrl || this.nextPageUrl === 'null' || this.nextPageUrl === '') {
            this.hasMore = false;
            this.hideLoader();
            const noMoreContent = document.querySelector('#no-more-content');
            if (noMoreContent && container.querySelectorAll('.recipe-card-item').length > 0) {
                noMoreContent.style.display = 'block';
            }
            return;
        }
        
        // Привязываем обработчик скролла
        this.bindScrollEvent();
        
        // Скрываем индикатор загрузки изначально
        this.hideLoader();
    }
    
    bindScrollEvent() {
        let scrollTimeout;
        
        window.addEventListener('scroll', () => {
            // Используем debounce для оптимизации
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.checkScroll();
            }, 100);
        });
    }
    
    checkScroll() {
        // Проверяем, достиг ли пользователь конца страницы
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;
        
        if (scrollPosition >= pageHeight - this.threshold && !this.isLoading && this.hasMore) {
            this.loadMore();
        }
    }
    
    async loadMore() {
        if (!this.nextPageUrl || this.isLoading || !this.hasMore) {
            return;
        }
        
        this.isLoading = true;
        this.showLoader();
        
        try {
            const response = await fetch(this.nextPageUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const html = await response.text();
            
            // Если HTML пустой или очень короткий, значит больше нет контента
            if (!html || html.trim().length < 50) {
                this.hasMore = false;
                this.hideLoader();
                this.showNoMoreContent();
                return;
            }
            
            // Создаем временный контейнер для парсинга HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Получаем новые карточки
            const newCards = temp.querySelectorAll('.recipe-card-item');
            
            if (newCards.length === 0) {
                this.hasMore = false;
                this.hideLoader();
                this.showNoMoreContent();
                return;
            }
            
            // Добавляем новые карточки в контейнер
            const container = document.querySelector(this.container);
            newCards.forEach(card => {
                container.appendChild(card);
                
                // Добавляем анимацию появления
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
            });
            
            // Обновляем URL следующей страницы
            this.updateNextPageUrl(response.url);
            
            // Если загрузили меньше карточек, чем ожидалось, значит это последняя страница
            if (newCards.length < 12) {
                this.hasMore = false;
                this.showNoMoreContent();
            }
            
        } catch (error) {
            console.error('Error loading more items:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.hideLoader();
        }
    }
    
    updateNextPageUrl(currentUrl) {
        // Извлекаем номер текущей страницы из URL
        const url = new URL(currentUrl, window.location.origin);
        const currentPage = parseInt(url.searchParams.get('page') || '1');
        const nextPage = currentPage + 1;
        
        // Формируем URL следующей страницы, всегда используя HTTPS для безопасности
        url.searchParams.set('page', nextPage);
        // Принудительно используем HTTPS если страница загружена через HTTPS
        if (window.location.protocol === 'https:') {
            url.protocol = 'https:';
        } else {
            url.protocol = window.location.protocol;
        }
        this.nextPageUrl = url.toString();
        
        // Проверяем, есть ли еще страницы
        // Если сервер вернул меньше элементов, чем ожидалось, значит это последняя страница
        const container = document.querySelector(this.container);
        const totalCards = container.querySelectorAll('.recipe-card-item').length;
        const cardsPerPage = 12;
        
        if (totalCards % cardsPerPage !== 0 && totalCards > 0) {
            this.hasMore = false;
        }
    }
    
    showLoader() {
        const loader = document.querySelector(this.loader);
        if (loader) {
            loader.style.display = 'flex';
        }
    }
    
    hideLoader() {
        const loader = document.querySelector(this.loader);
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    showError() {
        const loader = document.querySelector(this.loader);
        if (loader) {
            loader.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Ошибка загрузки. Попробуйте обновить страницу.
                </div>
            `;
        }
    }
    
    showNoMoreContent() {
        const noMoreContent = document.querySelector('#no-more-content');
        if (noMoreContent) {
            noMoreContent.style.display = 'block';
        }
    }
}
