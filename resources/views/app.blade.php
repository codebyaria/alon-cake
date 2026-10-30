<!DOCTYPE html>
{{-- Alon Cake is a light-only brand (CLAUDE.md > Visual): no dark class, ever. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="color-scheme: light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: #F6F0E6;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <meta name="theme-color" content="#F6F0E6">

        {{-- Crawlers and link previews don't run JavaScript, so these are rendered here. --}}
        @php
            $siteTitle = __('Kue ulang tahun, dibuat pelan-pelan').' - '.config('app.name');
            $description = __('Kue ulang tahun by order, dibuat satu per satu mulai tiga hari sebelum hari-H. Pilih tanggal, kue, dan tulisannya; kami kabari di setiap tahap.');
            $path = '/'.ltrim(preg_replace('#^/?en(?=/|$)#', '', request()->getPathInfo()), '/');
            $idUrl = url($path);
            $enUrl = url(rtrim('/en'.$path, '/'));
            // Order steps after the date live in a session draft; only the date step is worth indexing.
            $noindex = $page['component'] === 'playground'
                || (str_starts_with($page['component'], 'order/') && $page['component'] !== 'order/date');
        @endphp
        <meta name="description" content="{{ $description }}">
        @if ($noindex)
            <meta name="robots" content="noindex">
        @else
            <link rel="canonical" href="{{ app()->getLocale() === 'en' ? $enUrl : $idUrl }}">
            <link rel="alternate" hreflang="id" href="{{ $idUrl }}">
            <link rel="alternate" hreflang="en" href="{{ $enUrl }}">
            <link rel="alternate" hreflang="x-default" href="{{ $idUrl }}">
        @endif
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $siteTitle }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'id_ID' }}">
        <meta property="og:image" content="{{ asset('images/og.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ __('Kartu kecil bertulisan tangan di samping kue') }}">
        <meta name="twitter:card" content="summary_large_image">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ $siteTitle }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
