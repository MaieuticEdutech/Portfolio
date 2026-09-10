<x-layouts::app :title="config('app.name').' — Portfolio'">
    <header class="relative overflow-hidden border-b border-white/5">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-24 -top-32 size-96 rounded-full bg-brand-teal/25 blur-3xl"></div>
            <div class="absolute -right-24 top-10 size-80 rounded-full bg-brand-red/20 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-mint">Selected work</p>

            <h1 class="mt-4 max-w-3xl text-balance text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-6xl">
                Films for the people who <span class="bg-gradient-to-r from-brand-aqua via-brand-mint to-brand-teal bg-clip-text text-transparent">teach</span>
                and the brands who <span class="bg-gradient-to-r from-brand-peach via-brand-coral to-brand-red bg-clip-text text-transparent">build</span>.
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/60">
                {{ $totalClients }} clients across education and enterprise &mdash; explainers, brand films,
                campaign series and training libraries, produced end to end.
            </p>
        </div>
    </header>

    <main class="pt-8">
        <livewire:portfolio-grid />
    </main>

    <footer class="border-t border-white/5 py-10">
        <div class="mx-auto max-w-7xl px-4 text-sm text-white/35 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>
</x-layouts::app>
