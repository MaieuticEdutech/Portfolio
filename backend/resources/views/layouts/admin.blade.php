<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title ?? 'Studio' }} &middot; {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-ink-900 antialiased">
        @auth
            <header class="border-b border-white/5 bg-ink-800">
                <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3 sm:px-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm font-semibold text-white">
                        {{ config('app.name') }}
                        <span class="ml-1 rounded bg-brand-teal/20 px-1.5 py-0.5 text-[10px] uppercase tracking-wider text-brand-mint">Studio</span>
                    </a>

                    <a href="{{ route('portfolio') }}" class="text-sm text-white/40 transition hover:text-white">
                        View site
                    </a>

                    <div class="ml-auto flex items-center gap-4">
                        <span class="hidden text-sm text-white/40 sm:inline">{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-white/40 transition hover:text-brand-coral">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
                {{ $slot }}
            </main>
        @else
            <main class="flex min-h-screen items-center justify-center px-4">
                {{ $slot }}
            </main>
        @endauth

        @livewireScripts
    </body>
</html>
