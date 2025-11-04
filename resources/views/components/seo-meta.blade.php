{{-- SEO Meta Tags Component --}}

{{-- Title --}}
<title>{{ $seo['title'] ?? config('app.name') }}</title>

{{-- Meta Description --}}
@if(isset($seo['description']))
<meta name="description" content="{{ $seo['description'] }}">
@endif

{{-- Meta Keywords --}}
@if(isset($seo['keywords']))
<meta name="keywords" content="{{ $seo['keywords'] }}">
@endif

{{-- Canonical URL --}}
@if(isset($seo['canonical']))
<link rel="canonical" href="{{ $seo['canonical'] }}">
@endif

{{-- Robots (обновлено для 2025) --}}
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1">
<meta name="bingbot" content="index, follow">
<meta name="yandex" content="index, follow">

{{-- Google специфичные теги --}}
<meta name="google" content="notranslate">
<meta name="google-site-verification" content="">

{{-- Яндекс специфичные теги --}}
<meta name="yandex-verification" content="">
<meta name="cmsmagazine" content="">

{{-- Author & Copyright --}}
<meta name="author" content="{{ config('app.name') }}">
<meta name="copyright" content="© {{ date('Y') }} {{ config('app.name') }}">
<meta name="publisher" content="{{ config('app.name') }}">

{{-- Language & Geo --}}
<meta http-equiv="content-language" content="ru">
<meta name="language" content="Russian">
<meta name="geo.region" content="RU">
<meta name="geo.placename" content="Россия">

{{-- Rating & Content Type --}}
<meta name="rating" content="General">
<meta name="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

{{-- Referrer Policy для безопасности и отслеживания --}}
<meta name="referrer" content="strict-origin-when-cross-origin">

{{-- Open Graph Meta Tags (расширенные для 2025) --}}
@if(isset($seo['og']))
<meta property="og:title" content="{{ $seo['og']['title'] }}">
<meta property="og:description" content="{{ $seo['og']['description'] }}">
<meta property="og:url" content="{{ $seo['og']['url'] }}">
<meta property="og:type" content="{{ $seo['og']['type'] }}">
<meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
<meta property="og:locale" content="{{ $seo['og']['locale'] }}">
<meta property="og:locale:alternate" content="en_US">

@if(isset($seo['og']['image']))
<meta property="og:image" content="{{ $seo['og']['image'] }}">
<meta property="og:image:secure_url" content="{{ $seo['og']['image'] }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="{{ $seo['og']['image:width'] ?? 1200 }}">
<meta property="og:image:height" content="{{ $seo['og']['image:height'] ?? 630 }}">
<meta property="og:image:alt" content="{{ $seo['og']['image:alt'] ?? $seo['og']['title'] }}">
@endif

@if(isset($seo['og']['article:published_time']))
<meta property="article:published_time" content="{{ $seo['og']['article:published_time'] }}">
<meta property="article:modified_time" content="{{ $seo['og']['article:modified_time'] }}">
<meta property="article:section" content="{{ $seo['og']['article:section'] }}">
<meta property="article:tag" content="{{ $seo['og']['article:tag'] }}">
<meta property="article:author" content="{{ config('app.url') }}">
@endif

{{-- VK (ВКонтакте) специфичные теги --}}
<meta property="vk:image" content="{{ $seo['og']['image'] ?? '' }}">

{{-- OK (Одноклассники) специфичные теги --}}
<meta property="ok:title" content="{{ $seo['og']['title'] }}">
<meta property="ok:description" content="{{ $seo['og']['description'] }}">
<meta property="ok:image" content="{{ $seo['og']['image'] ?? '' }}">
@endif

{{-- Twitter Card Meta Tags (расширенные) --}}
@if(isset($seo['twitter']))
<meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
<meta name="twitter:title" content="{{ $seo['twitter']['title'] }}">
<meta name="twitter:description" content="{{ $seo['twitter']['description'] }}">
<meta name="twitter:site" content="@yaedok">
<meta name="twitter:creator" content="@yaedok">

@if(isset($seo['twitter']['image']))
<meta name="twitter:image" content="{{ $seo['twitter']['image'] }}">
<meta name="twitter:image:alt" content="{{ $seo['twitter']['image:alt'] ?? $seo['twitter']['title'] }}">
@endif
@endif

{{-- Яндекс.Дзен и Telegram специфичные теги (2025) --}}
@if(isset($seo['og']))
{{-- Яндекс.Дзен (Turbo Pages) --}}
<meta name="turbo-template" content="recipe">
<meta property="ya:ovs:upload_date" content="{{ $seo['og']['article:published_time'] ?? now()->toIso8601String() }}">
<meta property="ya:ovs:content_id" content="{{ url()->current() }}">
<meta property="ya:ovs:adult" content="false">

{{-- Telegram Instant View --}}
<meta property="telegram:channel" content="@imedokru">
<meta property="telegram:card" content="summary_large_image">

{{-- Социальные сети --}}
<link rel="me" href="https://dzen.ru/imedok">
<link rel="me" href="https://t.me/imedokru">
@endif

{{-- Favicon --}}
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/manifest.json">

{{-- RSS/Atom feeds (новое для 2025) --}}
<link rel="alternate" type="application/rss+xml" title="RSS лента рецептов" href="{{ route('rss.recipes') }}">
<link rel="alternate" type="application/atom+xml" title="Atom лента рецептов" href="{{ route('rss.atom') }}">
<link rel="alternate" type="application/rss+xml" title="Яндекс.Дзен" href="{{ route('rss.yandex-zen') }}">

{{-- humans.txt для 2025 --}}
<link rel="author" type="text/plain" href="/humans.txt">

{{-- Schema.org JSON-LD --}}
@if(isset($seo['schema']))
<script type="application/ld+json">
{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Breadcrumbs Schema --}}
@if(isset($seo['breadcrumbs']))
<script type="application/ld+json">
{!! json_encode($seo['breadcrumbs'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Additional Schema (ItemList for homepage) --}}
@if(isset($itemListSchema))
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Mobile Optimization (обновлено для 2025) --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0">
<meta name="theme-color" content="#ff6b6b" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#ff5252" media="(prefers-color-scheme: dark)">
<meta name="color-scheme" content="light dark">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<meta name="format-detection" content="telephone=no">
<meta name="mobile-web-app-capable" content="yes">

{{-- Performance Hints для Core Web Vitals --}}
<meta http-equiv="x-dns-prefetch-control" content="on">

{{-- DNS Prefetch & Preconnect (обновлено) --}}
<link rel="dns-prefetch" href="//fonts.bunny.net">
<link rel="dns-prefetch" href="//mc.yandex.ru">
<link rel="dns-prefetch" href="//www.googletagmanager.com">
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="preconnect" href="https://mc.yandex.ru" crossorigin>

{{-- Preload критических изображений --}}
@if(isset($seo['og']['image']))
<link rel="preload" as="image" href="{{ $seo['og']['image'] }}" fetchpriority="high" imagesrcset="{{ $seo['og']['image'] }}" imagesizes="100vw">
@endif
