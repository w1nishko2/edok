@extends('layouts.app')

@section('title', $title)
@section('description', $description)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <article class="prose lg:prose-xl max-w-none">
        <h1 class="text-4xl font-bold mb-8">Пользовательское соглашение</h1>
        
        <p class="text-gray-600 mb-6">Дата последнего обновления: {{ date('d.m.Y') }}</p>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">1. Общие положения</h2>
            <p class="mb-4">
                Настоящее Пользовательское соглашение (далее — «Соглашение») регулирует отношения между 
                владельцем сайта <strong>ЯЕдок</strong> (im-edok.ru) и пользователями сайта.
            </p>
            <p class="mb-4">
                Используя данный сайт, вы автоматически соглашаетесь с условиями настоящего Соглашения.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">2. Информация о владельце</h2>
            <p class="mb-4">
                <strong>Владелец сайта:</strong> Лукманов Даниил Равильевич<br>
                <strong>Статус:</strong> Самозанятый<br>
                <strong>Email:</strong> <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline">w1nishko@yandex.ru</a>
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">3. Предмет соглашения</h2>
            <p class="mb-4">
                Сайт ЯЕдок предоставляет пользователям доступ к кулинарным рецептам, статьям и другим материалам 
                (далее — «Контент»).
            </p>
            <p class="mb-4">
                Все материалы сайта предоставляются исключительно в информационных целях.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">4. Права и обязанности пользователя</h2>
            
            <h3 class="text-xl font-semibold mb-3">4.1. Пользователь имеет право:</h3>
            <ul class="list-disc pl-6 mb-4">
                <li>Просматривать материалы сайта</li>
                <li>Использовать рецепты для личных некоммерческих целей</li>
                <li>Делиться ссылками на материалы сайта</li>
            </ul>

            <h3 class="text-xl font-semibold mb-3">4.2. Пользователь обязуется:</h3>
            <ul class="list-disc pl-6 mb-4">
                <li>Не копировать и не публиковать материалы сайта без согласия администрации</li>
                <li>Не использовать автоматические средства для сбора информации с сайта</li>
                <li>Не предпринимать действий, которые могут нарушить работу сайта</li>
                <li>Соблюдать действующее законодательство Российской Федерации</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">5. Интеллектуальная собственность</h2>
            <p class="mb-4">
                Все материалы сайта, включая тексты, изображения, графику, дизайн и другие элементы, 
                являются объектами интеллектуальной собственности и защищены законодательством РФ.
            </p>
            <p class="mb-4">
                Использование материалов сайта в коммерческих целях без письменного разрешения владельца запрещено.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">6. Ответственность</h2>
            <p class="mb-4">
                Администрация сайта не несет ответственности за:
            </p>
            <ul class="list-disc pl-6 mb-4">
                <li>Результаты использования информации, размещенной на сайте</li>
                <li>Технические сбои и перерывы в работе сайта</li>
                <li>Действия третьих лиц, направленные на нарушение работы сайта</li>
            </ul>
            <p class="mb-4">
                Все рецепты и рекомендации предоставляются «как есть». Пользователь самостоятельно несет 
                ответственность за результаты их использования.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">7. Ссылки на сторонние ресурсы</h2>
            <p class="mb-4">
                Сайт может содержать ссылки на сторонние ресурсы. Администрация не несет ответственности 
                за содержание таких ресурсов и не контролирует их.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">8. Изменение условий соглашения</h2>
            <p class="mb-4">
                Администрация оставляет за собой право изменять условия настоящего Соглашения без 
                предварительного уведомления пользователей.
            </p>
            <p class="mb-4">
                Новая редакция Соглашения вступает в силу с момента ее размещения на сайте.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">9. Применимое право</h2>
            <p class="mb-4">
                Настоящее Соглашение регулируется законодательством Российской Федерации.
            </p>
            <p class="mb-4">
                Все споры решаются путем переговоров. При невозможности достижения соглашения споры 
                подлежат рассмотрению в судебном порядке в соответствии с законодательством РФ.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">10. Контакты</h2>
            <p class="mb-4">
                По всем вопросам, связанным с настоящим Соглашением, вы можете обратиться к нам:
            </p>
            <p class="mb-4">
                <strong>Email:</strong> <a href="mailto:w1nishko@yandex.ru" class="text-blue-600 hover:underline">w1nishko@yandex.ru</a><br>
                <strong>Telegram:</strong> <a href="https://t.me/imedokru" target="_blank" class="text-blue-600 hover:underline">@imedokru</a>
            </p>
        </section>
    </article>
</div>
@endsection
