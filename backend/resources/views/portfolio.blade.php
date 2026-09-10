<x-layouts::app :title="config('app.name').' — Portfolio'">
    <header class="relative overflow-hidden border-b border-ink-900/10">
        {{-- Slow brand aurora behind the copy. Pure CSS, no asset, and it stops
             the white page reading as a blank sheet. --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="hero-drift-one absolute -left-32 -top-40 size-[38rem] rounded-full bg-brand-mint/25 blur-3xl"></div>
            <div class="hero-drift-two absolute -right-24 -top-10 size-[32rem] rounded-full bg-brand-peach/30 blur-3xl"></div>
            <div class="hero-drift-three absolute -bottom-32 left-1/4 size-[28rem] rounded-full bg-brand-aqua/20 blur-3xl"></div>
        </div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 sm:py-24 lg:grid-cols-[1.05fr_1fr] lg:gap-16 lg:px-8">
            <div>
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

            {{-- Showreel: a real film once one is uploaded, and until then a panel
                 that moves under its own steam rather than an empty box. --}}
            <div class="relative aspect-[4/3] w-full overflow-hidden rounded-3xl shadow-2xl ring-1 ring-ink-900/10 sm:aspect-video lg:aspect-[4/3]">
                @if ($heroFilm)
                    <video
                        autoplay muted loop playsinline preload="metadata"
                        aria-hidden="true" tabindex="-1"
                        @if ($heroFilm->thumbnailUrl()) poster="{{ $heroFilm->thumbnailUrl() }}" @endif
                        class="size-full object-cover"
                    >
                        <source src="{{ $heroFilm->source() }}">
                    </video>

                    <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-5 pt-16">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.22em] text-white/70">Now showing</span>
                        <span class="mt-1 block text-lg font-semibold text-white">{{ $heroFilm->client->name }}</span>
                    </span>
                @else
                    {{-- A strip of film advancing behind the brand wash. Sprocket holes
                         and frame edges are gradients, so there is no asset to ship. --}}
                    <span aria-hidden="true" class="absolute inset-0 bg-[linear-gradient(120deg,#00615C_0%,#008680_38%,#15D9A1_52%,#008680_66%,#003D3A_100%)]"></span>
                    <span aria-hidden="true" class="hero-sheen absolute inset-0"></span>
                    <span aria-hidden="true" class="hero-frames absolute inset-x-0 top-1/2 h-1/2 -translate-y-1/2 opacity-70"></span>
                    <span aria-hidden="true" class="hero-sprockets absolute inset-x-0 top-4 h-4 opacity-80"></span>
                    <span aria-hidden="true" class="hero-sprockets absolute inset-x-0 bottom-4 h-4 opacity-80"></span>
                    <span aria-hidden="true" class="tile-grain absolute inset-0 opacity-25 mix-blend-overlay"></span>

                    <span class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-center">
                        <span class="hero-pulse grid size-14 place-items-center rounded-full bg-white/15 ring-1 ring-white/30 backdrop-blur-sm">
                            <svg class="size-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M8 5v14l11-7L8 5Z" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-white/90">Showreel</span>
                        <span class="max-w-[15rem] text-xs leading-relaxed text-white/60">
                            Upload a film in the studio and it plays here.
                        </span>
                    </span>
                @endif
            </div>
        </div>
    </header>

    {{-- Client wall: two counter-scrolling rows. On white these need real
         contrast, so the plates are solid rather than a tint of the page. --}}
    @if ($marqueeClients->isNotEmpty())
        <section aria-label="Clients" class="overflow-hidden border-b border-ink-900/10 bg-ink-900/[0.02] py-8">
            <div aria-hidden="true" data-logo-marquee class="select-none space-y-4 [mask-image:linear-gradient(90deg,transparent,black_12%,black_88%,transparent)]">
                @foreach ([['clients' => $marqueeClients, 'motion' => 'animate-marquee'], ['clients' => $marqueeClients->reverse(), 'motion' => 'animate-marquee-reverse']] as $row)
                    <div class="flex w-max gap-4 {{ $row['motion'] }}">
                        @foreach ([0, 1] as $copy)
                            @foreach ($row['clients'] as $client)
                                <div class="flex h-16 w-40 shrink-0 items-center justify-center rounded-xl bg-white px-4 shadow-sm ring-1 ring-ink-900/10 sm:h-20 sm:w-48">
                                    @if ($client->logoUrl())
                                        <img src="{{ $client->logoUrl() }}" alt="" loading="lazy" class="max-h-10 w-auto max-w-full object-contain sm:max-h-12">
                                    @else
                                        <span class="truncate text-center text-xs font-semibold tracking-wide text-ink-900/45">{{ $client->name }}</span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <main class="pt-12">
        <livewire:portfolio-grid />
    </main>

    <footer class="border-t border-ink-900/10 py-10">
        <div class="mx-auto max-w-7xl px-4 text-sm text-ink-900/40 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>
</x-layouts::app>
