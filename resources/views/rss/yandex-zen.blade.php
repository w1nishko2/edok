<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ config('app.name') }} - Рецепты</title>
        <link>{{ route('home') }}</link>
        <description>Новые рецепты с фото</description>
        <language>ru</language>
        <lastBuildDate>{{ $buildDate->toRssString() }}</lastBuildDate>

        @foreach($recipes as $recipe)
        <item>
            <title>{{ $recipe->title }}</title>
            <link>{{ route('recipe.show', $recipe->slug) }}</link>
            <pdalink>{{ route('recipe.show', $recipe->slug) }}</pdalink>
            <description>{{ $recipe->description }}</description>
            <pubDate>{{ $recipe->created_at->toRssString() }}</pubDate>
            <author>{{ config('app.name') }}</author>
            <category>Кулинария</category>
            
            @if($recipe->image_path)
            <enclosure url="{{ asset('storage/' . $recipe->image_path) }}" type="image/jpeg" />
            <yandex:full-text>
                <![CDATA[
                <img src="{{ asset('storage/' . $recipe->image_path) }}" alt="{{ $recipe->title }}">
                <p>{{ $recipe->description }}</p>
                @if($recipe->ingredients && count($recipe->ingredients) > 0)
                <h3>Ингредиенты:</h3>
                <ul>
                    @foreach($recipe->ingredients as $ingredient)
                    <li>{{ $ingredient['quantity'] ?? '' }} {{ $ingredient['measure'] ?? '' }} {{ $ingredient['name'] ?? '' }}</li>
                    @endforeach
                </ul>
                @endif
                ]]>
            </yandex:full-text>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
