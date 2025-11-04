<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" 
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }} - Рецепты с фото</title>
        <link>{{ route('home') }}</link>
        <description>Новые рецепты с пошаговыми фото и подробными инструкциями</description>
        <language>ru</language>
        <lastBuildDate>{{ $buildDate->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('rss.recipes') }}" rel="self" type="application/rss+xml" />
        <image>
            <url>{{ asset('/android-chrome-512x512.png') }}</url>
            <title>{{ config('app.name') }}</title>
            <link>{{ route('home') }}</link>
        </image>
        <copyright>© {{ date('Y') }} {{ config('app.name') }}</copyright>
        <managingEditor>{{ config('mail.from.address') }} ({{ config('app.name') }})</managingEditor>
        <webMaster>{{ config('mail.from.address') }} ({{ config('app.name') }})</webMaster>
        <ttl>60</ttl>

        @foreach($recipes as $recipe)
        <item>
            <title>{{ $recipe->title }}</title>
            <link>{{ route('recipe.show', $recipe->slug) }}</link>
            <guid isPermaLink="true">{{ route('recipe.show', $recipe->slug) }}</guid>
            <description><![CDATA[{{ $recipe->description }}]]></description>
            <pubDate>{{ $recipe->created_at->toRssString() }}</pubDate>
            <dc:creator>{{ config('app.name') }}</dc:creator>
            
            @if($recipe->image_path)
            <media:content url="{{ asset('storage/' . $recipe->image_path) }}" type="image/jpeg" medium="image">
                <media:title>{{ $recipe->title }}</media:title>
                <media:description>{{ $recipe->description }}</media:description>
            </media:content>
            <enclosure url="{{ asset('storage/' . $recipe->image_path) }}" type="image/jpeg" />
            @endif

            <content:encoded><![CDATA[
                @if($recipe->image_path)
                <img src="{{ asset('storage/' . $recipe->image_path) }}" alt="{{ $recipe->title }}" style="max-width: 100%; height: auto;">
                @endif
                <p>{{ $recipe->description }}</p>
                @if($recipe->ingredients && count($recipe->ingredients) > 0)
                <h3>Ингредиенты:</h3>
                <ul>
                    @foreach($recipe->ingredients as $ingredient)
                    <li>{{ $ingredient['quantity'] ?? '' }} {{ $ingredient['measure'] ?? '' }} {{ $ingredient['name'] ?? '' }}</li>
                    @endforeach
                </ul>
                @endif
                <p><a href="{{ route('recipe.show', $recipe->slug) }}">Читать полный рецепт →</a></p>
            ]]></content:encoded>

            @if($recipe->prep_time || $recipe->cook_time || $recipe->servings)
            <category>
                @if($recipe->prep_time)Подготовка: {{ $recipe->prep_time }} мин. @endif
                @if($recipe->cook_time)Приготовление: {{ $recipe->cook_time }} мин. @endif
                @if($recipe->servings)Порций: {{ $recipe->servings }}@endif
            </category>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
