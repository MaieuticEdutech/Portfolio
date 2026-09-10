<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title ?? 'Studio' }} &middot; {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        {{-- Kept here rather than in app.css so studio work never touches the public stylesheet. --}}
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="min-h-screen bg-ink-900 antialiased">
        @auth
            <header class="border-b border-white/5 bg-ink-800">
                <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3 sm:px-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm font-semibold text-white">
                        {{ config('app.name') }}
                        <span class="ml-1 rounded bg-brand-teal/20 px-1.5 py-0.5 text-[10px] uppercase tracking-wider text-brand-mint">Studio</span>
                    </a>

                    <a href="{{ route('admin.clients') }}" wire:navigate class="text-sm text-white/40 transition hover:text-white">
                        Clients
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

        <div
            x-data="{ show: false, message: '' }"
            x-on:toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition.opacity
            x-cloak
            role="status"
            aria-live="polite"
            class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-full border border-brand-teal/40 bg-ink-800 px-5 py-2.5 text-sm text-white shadow-xl"
        >
            <span x-text="message"></span>
        </div>

        @livewireScripts
    </body>
</html>
