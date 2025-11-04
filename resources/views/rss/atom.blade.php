<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ config('app.name') }} - Рецепты с фото</title>
    <link href="{{ route('home') }}" rel="alternate" />
    <link href="{{ route('rss.atom') }}" rel="self" />
    <id>{{ route('home') }}</id>
    <updated>{{ $updated->toAtomString() }}</updated>
    <subtitle>Новые рецепты с пошаговыми фото и подробными инструкциями</subtitle>
    <author>
        <name>{{ config('app.name') }}</name>
        <email>{{ config('mail.from.address') }}</email>
        <uri>{{ route('home') }}</uri>
    </author>
    <rights>© {{ date('Y') }} {{ config('app.name') }}</rights>
    <icon>{{ asset('/favicon.ico') }}</icon>
    <logo>{{ asset('/android-chrome-512x512.png') }}</logo>

    @foreach($recipes as $recipe)
    <entry>
        <title>{{ $recipe->title }}</title>
        <link href="{{ route('recipe.show', $recipe->slug) }}" rel="alternate" />
        <id>{{ route('recipe.show', $recipe->slug) }}</id>
        <updated>{{ $recipe->updated_at->toAtomString() }}</updated>
        <published>{{ $recipe->created_at->toAtomString() }}</published>
        <summary>{{ $recipe->description }}</summary>
        
        @if($recipe->image_path)
        <link rel="enclosure" 
              type="image/jpeg" 
              href="{{ asset('storage/' . $recipe->image_path) }}" />
        @endif

        <content type="html"><![CDATA[
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
        ]]></content>

        <author>
            <name>{{ config('app.name') }}</name>
        </author>
    </entry>
    @endforeach
</feed>
