<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Agriculture Tractor') }}</title>
        @include('partials.pwa-head')

        <!-- Fonts - with font-display: swap for faster rendering -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gradient-to-br from-slate-100 via-indigo-50 to-slate-100 font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
            <div>
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-indigo-600" />
                </a>
            </div>

            <div class="mt-6 w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/95 px-6 py-6 shadow-sm backdrop-blur">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>