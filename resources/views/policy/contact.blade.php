@extends('layouts.app')

@section('title', $title)
@section('description', $description)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <article class="prose lg:prose-xl max-w-none">
        <h1 class="text-4xl font-bold mb-8">Контакты</h1>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">О проекте ЯЕдок</h2>
            <p class="mb-4">
                <strong>ЯЕдок</strong> — это кулинарный портал, где вы найдете тысячи проверенных рецептов 
                на любой вкус. Мы собираем лучшие рецепты из различных источников и делимся ими с вами.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Связь с нами</h2>
            
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold">Email</h3>
                    </div>
                    <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline text-lg">
                        w1nishko@yandex.ru
                    </a>
                    <p class="text-gray-600 mt-2">Для деловых предложений и обратной связи</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18.717-.962 4.038-1.36 5.358-.168.558-.5.746-.82.764-.696.064-1.226-.46-1.901-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.781-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.248-.024c-.106.024-1.793 1.14-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.245-1.349-.374-1.297-.789.027-.216.324-.437.892-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.14.121.099.155.232.171.326.016.094.036.308.02.475z"/>
                        </svg>
                        <h3 class="text-xl font-semibold">Telegram</h3>
                    </div>
                    <a href="https://t.me/imedokru" target="_blank" class="text-blue-600 hover:underline text-lg">
                        @imedokru
                    </a>
                    <p class="text-gray-600 mt-2">Наш канал с новыми рецептами</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 mr-3 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10zm1-11V7h-2v4H7l5 5 5-5h-4z"/>
                        </svg>
                        <h3 class="text-xl font-semibold">Яндекс.Дзен</h3>
                    </div>
                    <a href="https://dzen.ru/imedok" target="_blank" class="text-blue-600 hover:underline text-lg">
                        dzen.ru/imedok
                    </a>
                    <p class="text-gray-600 mt-2">Читайте наши статьи в Дзене</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        <h3 class="text-xl font-semibold">Сайт</h3>
                    </div>
                    <a href="{{ url('/') }}" class="text-blue-600 hover:underline text-lg">
                        im-edok.ru
                    </a>
                    <p class="text-gray-600 mt-2">Кулинарный портал с рецептами</p>
                </div>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Информация о владельце</h2>
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <p class="mb-2"><strong>ИП:</strong> Лукманов Даниил Равильевич</p>
                <p class="mb-2"><strong>Статус:</strong> Самозанятый</p>
                <p class="mb-2"><strong>Email:</strong> <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline">w1nishko@yandex.ru</a></p>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Документы</h2>
            <div class="space-y-3">
                <a href="{{ route('privacy.policy') }}" class="flex items-center text-blue-600 hover:underline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Политика конфиденциальности
                </a>
                <a href="{{ route('terms') }}" class="flex items-center text-blue-600 hover:underline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Пользовательское соглашение
                </a>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Обратная связь</h2>
            <p class="mb-4">
                Мы всегда рады вашим отзывам, предложениям и пожеланиям! Если у вас есть вопросы 
                или замечания по работе сайта, свяжитесь с нами любым удобным способом.
            </p>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-blue-800">
                    <strong>📧 Пишите нам:</strong> <a href="mailto:w1nishko@yandex.ru" class="underline">w1nishko@yandex.ru</a>
                </p>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Наши каналы</h2>
            <p class="mb-4">
                Подписывайтесь на наши каналы, чтобы не пропустить новые рецепты и кулинарные секреты:
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="https://t.me/imedokru" target="_blank" 
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18.717-.962 4.038-1.36 5.358-.168.558-.5.746-.82.764-.696.064-1.226-.46-1.901-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.781-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.248-.024c-.106.024-1.793 1.14-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.245-1.349-.374-1.297-.789.027-.216.324-.437.892-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.14.121.099.155.232.171.326.016.094.036.308.02.475z"/>
                    </svg>
                    Telegram канал
                </a>
                <a href="https://dzen.ru/imedok" target="_blank" 
                   class="inline-flex items-center px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10zm1-11V7h-2v4H7l5 5 5-5h-4z"/>
                    </svg>
                    Яндекс.Дзен
                </a>
            </div>
        </section>
    </article>
</div>
@endsection
