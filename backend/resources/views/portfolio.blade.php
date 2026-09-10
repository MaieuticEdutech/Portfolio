<x-layouts::app :title="config('app.name').' — Portfolio'">
    <header class="relative overflow-hidden border-b border-ink-900/10">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-24 -top-32 size-96 rounded-full bg-brand-mint/25 blur-3xl"></div>
            <div class="absolute -right-24 top-10 size-80 rounded-full bg-brand-peach/30 blur-3xl"></div>
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
                                <div class="flex h-16 w-44 shrink-0 items-center justify-center rounded-2xl bg-ink-900/[0.03] px-5 ring-1 ring-ink-900/[0.06] sm:h-20 sm:w-52">
                                    @if ($client->logoUrl())
                                        <span class="flex h-10 max-w-full items-center justify-center overflow-hidden rounded-lg bg-white px-2 py-1 sm:h-12 sm:px-2.5">
                                            <img src="{{ $client->logoUrl() }}" alt="" loading="lazy" class="h-full w-auto max-w-full object-contain">
                                        </span>
                                    @else
                                        <span class="truncate text-center text-xs font-semibold tracking-wide text-ink-900/25">{{ $client->name }}</span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- Keeps the headline crisp where it overlaps the wall --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-white via-white/80 to-white/30"></div>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-teal">Selected work</p>

            <h1 class="mt-4 max-w-3xl text-balance text-4xl font-bold leading-[1.05] tracking-tight text-ink-900 sm:text-6xl">
                Films for the people who <span class="bg-gradient-to-r from-brand-mint via-brand-teal to-brand-deep bg-clip-text text-transparent">teach</span>
                and the brands who <span class="bg-gradient-to-r from-brand-coral via-brand-red to-brand-rust bg-clip-text text-transparent">build</span>.
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-ink-900/60">
                {{ $totalClients }} clients across education and enterprise &mdash; explainers, brand films,
                campaign series and training libraries, produced end to end.
            </p>
        </div>
    </header>

    <main class="pt-8">
        <livewire:portfolio-grid />
    </main>

    <footer class="border-t border-ink-900/10 py-10">
        <div class="mx-auto max-w-7xl px-4 text-sm text-ink-900/40 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>
</x-layouts::app>
