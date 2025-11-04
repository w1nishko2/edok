<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (isset($seo))
        @include('components.seo-meta', ['seo' => $seo, 'itemListSchema' => $itemListSchema ?? null])
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
    @endif
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/css/recipes.css', 'resources/css/navbar.css', 'resources/js/app.js'])
    
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(100639873, 'init', {webvisor:true, clickmap:true, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/100639873" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->



</head>
<body class="@yield('body-class')">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <span>🍽️ {{ config('app.name', 'Laravel') }}</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Открыть меню">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Левое меню -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-house-fill"></i>
                                <span>Главная</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('categories*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                <i class="bi bi-collection-fill"></i>
                                <span>Категории</span>
                            </a>
                        </li>
                        @auth
                            @if (Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ Request::is('admin*') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                                        <i class="bi bi-gear-fill"></i>
                                        <span>Админка</span>
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Правое меню (только для авторизованных пользователей-админов) -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" 
                                   href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i>
                                    <span class="ms-1">{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right"></i> Выход
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
        <main class="py-4">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-dark text-light py-5 mt-5">
            <div class="container">
                <div class="row">
                    <!-- О проекте -->
                    <div class="col-md-4 mb-4">
                        <h5 class="mb-3">🍽️ {{ config('app.name') }}</h5>
                        <p class="text-muted">
                            Кулинарный портал с тысячами проверенных рецептов на любой вкус. 
                            Готовьте с удовольствием!
                        </p>
                    </div>

                    <!-- Навигация -->
                    <div class="col-md-2 mb-4">
                        <h6 class="mb-3">Навигация</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="{{ route('home') }}" class="text-muted text-decoration-none hover-link">Главная</a></li>
                            <li class="mb-2"><a href="{{ route('categories.index') }}" class="text-muted text-decoration-none hover-link">Категории</a></li>
                            <li class="mb-2"><a href="{{ route('contact') }}" class="text-muted text-decoration-none hover-link">Контакты</a></li>
                        </ul>
                    </div>

                    <!-- Документы -->
                    <div class="col-md-3 mb-4">
                        <h6 class="mb-3">Документы</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="{{ route('privacy.policy') }}" class="text-muted text-decoration-none hover-link">Политика конфиденциальности</a></li>
                            <li class="mb-2"><a href="{{ route('terms') }}" class="text-muted text-decoration-none hover-link">Пользовательское соглашение</a></li>
                        </ul>
                    </div>

                    <!-- Социальные сети -->
                    <div class="col-md-3 mb-4">
                        <h6 class="mb-3">Мы в соцсетях</h6>
                        <div class="d-flex flex-column gap-2">
                            <a href="https://t.me/imedokru" target="_blank" class="text-muted text-decoration-none hover-link d-flex align-items-center">
                                <i class="bi bi-telegram me-2"></i> Telegram
                            </a>
                            <a href="https://dzen.ru/imedok" target="_blank" class="text-muted text-decoration-none hover-link d-flex align-items-center">
                                <i class="bi bi-browser-chrome me-2"></i> Яндекс.Дзен
                            </a>
                            <a href="mailto:w1nishko@yandex.ru" class="text-muted text-decoration-none hover-link d-flex align-items-center">
                                <i class="bi bi-envelope me-2"></i> Email
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="my-4 bg-secondary">

                <div class="row">
                    <div class="col-md-6 text-muted small">
                        <p class="mb-0">
                            © {{ date('Y') }} {{ config('app.name') }}. Все права защищены.<br>
                            ИП: Лукманов Даниил Равильевич (Самозанятый)
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end text-muted small">
                        <p class="mb-0">
                            Сделано с ❤️ для любителей готовить
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Кнопка "Назад" для мобильных -->
    <button class="btn-back-mobile" onclick="window.history.back()" aria-label="Вернуться назад">
        <i class="bi bi-arrow-left"></i>
    </button>

    <script>
        // Скрываем кнопку "Назад" если нет истории
        window.addEventListener('DOMContentLoaded', function() {
            const backBtn = document.querySelector('.btn-back-mobile');
            if (backBtn && window.history.length <= 1) {
                backBtn.style.display = 'none';
            }
        });
    </script>
</body>
</html>
