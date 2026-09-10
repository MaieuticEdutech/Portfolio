<x-layouts::app :title="config('app.name').' — Portfolio'">
    <header class="relative overflow-hidden border-b border-white/5">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-24 -top-32 size-96 rounded-full bg-brand-teal/25 blur-3xl"></div>
            <div class="absolute -right-24 top-10 size-80 rounded-full bg-brand-red/20 blur-3xl"></div>
        </div>

        {{-- Client wall: two slow counter-scrolling rows behind the copy. Logos appear
             as they are uploaded; until then each card carries the client name. --}}
        @if ($marqueeClients->isNotEmpty())
            <div
                aria-hidden="true"
                data-logo-marquee
                class="pointer-events-none absolute inset-x-0 top-1/2 -z-10 -translate-y-1/2 select-none space-y-5 [mask-image:linear-gradient(90deg,transparent,black_15%,black_85%,transparent)]"
            >
                @foreach ([['clients' => $marqueeClients, 'motion' => 'animate-marquee'], ['clients' => $marqueeClients->reverse(), 'motion' => 'animate-marquee-reverse']] as $row)
                    <div class="flex w-max gap-5 {{ $row['motion'] }}">
                        @foreach ([0, 1] as $copy)
                            @foreach ($row['clients'] as $client)
                                <div class="flex h-16 w-44 shrink-0 items-center justify-center rounded-2xl bg-white/[0.035] px-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] ring-1 ring-white/[0.06] sm:h-20 sm:w-52">
                                    @if ($client->logoUrl())
                                        <span class="flex h-10 max-w-full items-center justify-center overflow-hidden rounded-lg bg-white/85 px-2 py-1 sm:h-12 sm:px-2.5">
                                            <img src="{{ $client->logoUrl() }}" alt="" loading="lazy" class="h-full w-auto max-w-full object-contain">
                                        </span>
                                    @else
                                        <span class="truncate text-center text-xs font-semibold tracking-wide text-white/25">{{ $client->name }}</span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- Keeps the headline crisp where it overlaps the wall --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-ink-900 via-ink-900/75 to-ink-900/20"></div>
        @endif

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
