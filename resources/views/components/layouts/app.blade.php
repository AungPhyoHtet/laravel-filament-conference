@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Discover upcoming conferences, the speakers giving them and the talks on the programme.">

        <title>{{ $title }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-800/70 dark:bg-slate-950/80">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8" aria-label="Main">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                    <span class="grid size-8 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">C</span>
                    {{ config('app.name') }}
                </a>

                <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex dark:text-slate-300">
                    <a href="{{ route('home') }}#conferences" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Conferences</a>
                    <a href="{{ route('home') }}#speakers" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Speakers</a>
                    <a href="{{ route('home') }}#talks" class="transition hover:text-indigo-600 dark:hover:text-indigo-400">Talks</a>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('filament.app.auth.login') }}" class="hidden text-sm font-medium text-slate-600 transition hover:text-indigo-600 sm:block dark:text-slate-300 dark:hover:text-indigo-400">
                        Organizer sign in
                    </a>
                    <a href="{{ route('sign-up') }}" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        Sign up
                    </a>
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        @filamentScripts

        <footer class="border-t border-slate-200 dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-10 text-sm text-slate-500 sm:flex-row lg:px-8 dark:text-slate-400">
                <p>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="{{ route('home') }}#conferences" class="hover:text-slate-900 dark:hover:text-white">Conferences</a>
                    <a href="{{ route('home') }}#speakers" class="hover:text-slate-900 dark:hover:text-white">Speakers</a>
                    <a href="{{ route('filament.app.auth.login') }}" class="hover:text-slate-900 dark:hover:text-white">Organizers</a>
                </div>
            </div>
        </footer>
    </body>
</html>
