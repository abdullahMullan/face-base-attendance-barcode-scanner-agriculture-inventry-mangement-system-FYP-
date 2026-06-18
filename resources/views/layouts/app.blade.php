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

        @stack('styles')
        
        <!-- Bootstrap Icons with defer loading -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-100 via-indigo-50 to-slate-100">
        <div class="min-h-screen pb-6">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="mx-3 mt-3 rounded-2xl border border-slate-200/80 bg-white/80 shadow-sm backdrop-blur">
                    <div class="page-wrap py-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="mt-4">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
