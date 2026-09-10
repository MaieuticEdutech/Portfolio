<x-layouts::app :title="config('app.name').' — Portfolio'">
    <header class="relative overflow-hidden border-b border-ink-900/10">
        {{-- Slow brand aurora behind the copy. Pure CSS, no asset, and it stops
             the white page reading as a blank sheet. --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="hero-drift-one absolute -left-32 -top-40 size-[38rem] rounded-full bg-brand-mint/12 blur-3xl"></div>
            <div class="hero-drift-two absolute -right-24 -top-10 size-[32rem] rounded-full bg-brand-peach/15 blur-3xl"></div>
            <div class="hero-drift-three absolute -bottom-32 left-1/4 size-[28rem] rounded-full bg-brand-aqua/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="@if ($heroFilm) grid items-center gap-12 lg:grid-cols-[1.05fr_1fr] lg:gap-16 @endif">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-teal">Selected work</p>

                    {{-- With no showreel the headline takes the full measure rather
                         than leaving half the row to a panel announcing emptiness. --}}
                    <h1 class="mt-5 text-balance font-bold leading-[1.02] tracking-tight text-ink-900 {{ $heroFilm ? 'max-w-3xl text-4xl sm:text-6xl' : 'max-w-5xl text-5xl sm:text-7xl lg:text-8xl' }}">
                        Films for the people who <span class="bg-gradient-to-r from-brand-mint via-brand-teal to-brand-deep bg-clip-text text-transparent">teach</span>
                        and the brands who <span class="bg-gradient-to-r from-brand-coral via-brand-red to-brand-rust bg-clip-text text-transparent">build</span>.
                    </h1>

                    <p class="mt-8 max-w-2xl text-lg leading-relaxed text-ink-900/60">
                        {{ $totalClients }} clients across education and enterprise &mdash; explainers, brand films,
                        campaign series and training libraries, produced end to end.
                    </p>
                </div>

                {{-- The panel only exists when there is a real film to put in it.
                     A placeholder here would advertise having nothing to show. --}}
                @if ($heroFilm)
                    <div class="relative aspect-video w-full overflow-hidden rounded-3xl shadow-2xl ring-1 ring-ink-900/10 lg:aspect-[4/3]">
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
                    </div>
                @endif
            </div>
        </div>
    </header>

    {{-- Client wall: two counter-scrolling rows. On white these need real
         contrast, so the plates are solid rather than a tint of the page. --}}
    @if ($marqueeClients->isNotEmpty())
        <section aria-label="Clients" class="overflow-hidden border-b border-ink-900/10 bg-ink-900/[0.02] py-10">
            <p class="mb-6 text-center text-[11px] font-semibold uppercase tracking-[0.25em] text-ink-900/35">
                Trusted by {{ $totalClients }} education and enterprise brands
            </p>

            <div aria-hidden="true" data-logo-marquee class="select-none space-y-4 [mask-image:linear-gradient(90deg,transparent,black_12%,black_88%,transparent)]">
                @foreach ([['clients' => $marqueeClients, 'motion' => 'animate-marquee'], ['clients' => $marqueeClients->reverse(), 'motion' => 'animate-marquee-reverse']] as $row)
                    <div class="flex w-max gap-4 {{ $row['motion'] }}">
                        @foreach ([0, 1] as $copy)
                            @foreach ($row['clients'] as $client)
                                <div class="flex h-20 w-48 shrink-0 items-center justify-center rounded-xl bg-white px-5 shadow-sm ring-1 ring-ink-900/10 sm:h-24 sm:w-56">
                                    @if ($client->logoUrl())
                                        <img src="{{ $client->logoUrl() }}" alt="" loading="lazy" class="max-h-12 w-auto max-w-full object-contain sm:max-h-14">
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
