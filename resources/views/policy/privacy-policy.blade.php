@extends('layouts.app')

@section('title', $title)
@section('description', $description)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <article class="prose lg:prose-xl max-w-none">
        <h1 class="text-4xl font-bold mb-8">Политика конфиденциальности</h1>
        
        <p class="text-gray-600 mb-6">Дата последнего обновления: {{ date('d.m.Y') }}</p>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">1. Общие положения</h2>
            <p class="mb-4">
                Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных 
                пользователей сайта <strong>ЯЕдок</strong> (im-edok.ru).
            </p>
            <p class="mb-4">
                Используя наш сайт, вы соглашаетесь с условиями данной Политики конфиденциальности.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">2. Информация об операторе</h2>
            <p class="mb-4">
                <strong>Оператор:</strong> Лукманов Даниил Равильевич<br>
                <strong>Статус:</strong> Самозанятый<br>
                <strong>Email:</strong> <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline">w1nishko@yandex.ru</a>
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">3. Какие данные мы собираем</h2>
            <p class="mb-4">При использовании нашего сайта мы можем собирать следующую информацию:</p>
            <ul class="list-disc pl-6 mb-4">
                <li>IP-адрес</li>
                <li>Информация из cookies</li>
                <li>Информация о браузере и устройстве</li>
                <li>Страницы, которые вы посещаете</li>
                <li>Время и дата посещения</li>
                <li>Email-адрес (при регистрации или подписке)</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">4. Как мы используем ваши данные</h2>
            <p class="mb-4">Мы используем собранную информацию для:</p>
            <ul class="list-disc pl-6 mb-4">
                <li>Улучшения работы сайта и пользовательского опыта</li>
                <li>Анализа посещаемости и поведения пользователей</li>
                <li>Отправки уведомлений (если вы подписались)</li>
                <li>Обеспечения безопасности сайта</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">5. Cookies и веб-аналитика</h2>
            <p class="mb-4">
                Наш сайт использует cookies для улучшения функциональности и анализа трафика.
            </p>
            <p class="mb-4">
                <strong>Яндекс.Метрика:</strong> Мы используем систему веб-аналитики Яндекс.Метрика для сбора статистики 
                о посещаемости сайта. Яндекс.Метрика использует cookies для отслеживания поведения пользователей. 
                Собранные данные являются анонимными и используются только в статистических целях.
            </p>
            <p class="mb-4">
                Подробнее о политике конфиденциальности Яндекс.Метрики можно узнать на 
                <a href="https://yandex.ru/legal/confidential/" target="_blank" class="text-blue-600 hover:underline">официальном сайте Яндекса</a>.
            </p>
            <p class="mb-4">
                Вы можете отключить cookies в настройках вашего браузера, однако это может повлиять на функциональность сайта.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">6. Защита персональных данных</h2>
            <p class="mb-4">
                Мы принимаем все необходимые меры для защиты ваших персональных данных от несанкционированного доступа, 
                изменения, раскрытия или уничтожения.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">7. Передача данных третьим лицам</h2>
            <p class="mb-4">
                Мы не передаем ваши персональные данные третьим лицам, за исключением случаев:
            </p>
            <ul class="list-disc pl-6 mb-4">
                <li>Когда это необходимо для предоставления услуг (например, хостинг-провайдер)</li>
                <li>По требованию законодательства Российской Федерации</li>
                <li>С вашего явного согласия</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">8. Ваши права</h2>
            <p class="mb-4">Вы имеете право:</p>
            <ul class="list-disc pl-6 mb-4">
                <li>Получить информацию о собранных данных</li>
                <li>Запросить удаление ваших персональных данных</li>
                <li>Отозвать согласие на обработку данных</li>
                <li>Обжаловать действия оператора в уполномоченном органе</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">9. Изменения в Политике конфиденциальности</h2>
            <p class="mb-4">
                Мы оставляем за собой право вносить изменения в данную Политику конфиденциальности. 
                Все изменения вступают в силу с момента их публикации на сайте.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">10. Контакты</h2>
            <p class="mb-4">
                Если у вас есть вопросы относительно нашей Политики конфиденциальности, 
                вы можете связаться с нами:
            </p>
            <p class="mb-4">
                <strong>Email:</strong> <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline">w1nishko@yandex.ru</a><br>
                <strong>Telegram:</strong> <a href="https://t.me/imedokru" target="_blank" class="text-blue-600 hover:underline">@imedokru</a>
            </p>
        </section>
    </article>
</div>
@endsection
