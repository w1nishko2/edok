/**
 * Cookie Consent Modal
 * Модальное окно для согласия с политикой cookies и конфиденциальности
 */

class CookieConsent {
    constructor() {
        this.cookieName = 'cookie_consent';
        this.cookieExpireDays = 365;
        this.init();
    }

    init() {
        // Проверяем, дал ли пользователь согласие ранее
        if (!this.hasConsent()) {
            this.showModal();
        }
    }

    hasConsent() {
        return this.getCookie(this.cookieName) === 'true';
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
    }

    showModal() {
        // Создаем модальное окно
        const modal = document.createElement('div');
        modal.id = 'cookie-consent-modal';
        modal.className = 'cookie-consent-modal';
        modal.innerHTML = `
            <div class="cookie-consent-overlay"></div>
            <div class="cookie-consent-content">
                <div class="cookie-consent-header">
                    <h3 class="cookie-consent-title">🍪 Использование Cookies</h3>
                </div>
                <div class="cookie-consent-body">
                    <p class="cookie-consent-text">
                        Мы используем cookies для улучшения работы сайта и анализа посещаемости. 
                        Продолжая использовать наш сайт, вы соглашаетесь с нашей 
                        <a href="/privacy-policy" class="cookie-consent-link" target="_blank">Политикой конфиденциальности</a> 
                        и <a href="/terms" class="cookie-consent-link" target="_blank">Условиями использования</a>.
                    </p>
                    <p class="cookie-consent-text-small">
                        На нашем сайте используется Яндекс.Метрика для сбора анонимной статистики посещений.
                    </p>
                </div>
                <div class="cookie-consent-footer">
                    <button id="cookie-consent-accept" class="cookie-consent-btn cookie-consent-btn-primary">
                        Принять
                    </button>
                    <button id="cookie-consent-reject" class="cookie-consent-btn cookie-consent-btn-secondary">
                        Отклонить
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Добавляем обработчики событий
        document.getElementById('cookie-consent-accept').addEventListener('click', () => {
            this.acceptConsent();
        });

        document.getElementById('cookie-consent-reject').addEventListener('click', () => {
            this.rejectConsent();
        });

        // Блокируем прокрутку страницы
        document.body.style.overflow = 'hidden';
    }

    hideModal() {
        const modal = document.getElementById('cookie-consent-modal');
        if (modal) {
            modal.classList.add('cookie-consent-fade-out');
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
            }, 300);
        }
    }

    acceptConsent() {
        this.setCookie(this.cookieName, 'true', this.cookieExpireDays);
        this.hideModal();
        
        // Здесь можно инициализировать метрики и другие скрипты
        this.initializeAnalytics();
    }

    rejectConsent() {
        this.setCookie(this.cookieName, 'false', 30); // Храним отказ 30 дней
        this.hideModal();
    }

    initializeAnalytics() {
        // Здесь можно инициализировать Яндекс.Метрику и другие системы аналитики
        console.log('Analytics initialized');
    }
}

// Экспортируем класс
export default CookieConsent;
