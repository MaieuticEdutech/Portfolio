@props(['clients'])

@php
    // Art direction, not decoration: four asymmetric corner treatments cycled by
    // index so no two neighbours share a silhouette, and the row reads as a
    // composed spread rather than a row of identical rounded rectangles.
    $shapes = [
        'rounded-[8rem_2rem_8rem_2rem]',
        'rounded-[2rem_9rem_2rem_9rem]',
        'rounded-[6rem_6rem_1.5rem_6rem]',
        'rounded-[1.5rem_6rem_6rem_6rem]',
    ];

    // Widths alternate so the track has rhythm and a card is always caught
    // mid-entry at the right edge.
    $widths = [
        'w-[78vw] sm:w-[58vw] lg:w-[42vw] xl:w-[34vw]',
        'w-[72vw] sm:w-[46vw] lg:w-[30vw] xl:w-[24vw]',
        'w-[78vw] sm:w-[54vw] lg:w-[38vw] xl:w-[30vw]',
        'w-[72vw] sm:w-[50vw] lg:w-[34vw] xl:w-[27vw]',
    ];
@endphp

<section data-hgallery aria-label="Selected films" class="relative bg-white">
    <div
        data-hgallery-viewport
        class="flex h-screen items-center overflow-hidden"
    >
        <div
            data-hgallery-track
            class="flex w-max items-center gap-8 px-[6vw] will-change-transform sm:gap-12 lg:gap-16 lg:px-[10vw]"
        >
            {{-- Opening plate: the section states itself, then travels away --}}
            <div class="w-[70vw] shrink-0 sm:w-[40vw] lg:w-[26vw]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-teal">The work</p>
                <h2 class="mt-5 text-balance text-4xl font-bold leading-[1.05] tracking-tight text-ink-900 sm:text-5xl lg:text-6xl">
                    Scroll<span class="text-ink-900/30">&mdash;</span>through the reel.
                </h2>
                <p class="mt-6 text-base leading-relaxed text-ink-900/55">
                    Explainers, brand films, campaign series and training libraries,
                    produced end to end for {{ $clients->count() }} clients.
                </p>
            </div>

            @foreach ($clients as $index => $client)
                @php
                    $stops = $client->gradientStops();
                    $highlight = $stops[0] ?? '#0C1817';
                    $tone = $stops[count($stops) - 1] ?? $highlight;
                    $gradient = "radial-gradient(85% 70% at 0% 0%, {$highlight} 0%, transparent 62%), "
                        ."linear-gradient(150deg, color-mix(in oklab, {$tone} 62%, #0C1817) 0%, #0C1817 78%)";
                    $isEducational = $client->category === 'educational';
                    $preview = $client->previewVideo;
                @endphp

                <article
                    data-hgallery-card
                    wire:key="hgal-{{ $client->id }}"
                    class="group relative shrink-0 {{ $widths[$index % count($widths)] }}"
                >
                    <div class="relative aspect-[4/5] w-full overflow-hidden shadow-2xl ring-1 ring-ink-900/10 {{ $shapes[$index % count($shapes)] }}">
                        <span class="absolute inset-0" style="background: {{ $gradient }}"></span>
                        <span class="tile-grain absolute inset-0 opacity-30 mix-blend-overlay"></span>

                        @if ($preview)
                            <video
                                muted loop playsinline preload="none"
                                aria-hidden="true" tabindex="-1"
                                @if ($preview->thumbnailUrl()) poster="{{ $preview->thumbnailUrl() }}" @endif
                                class="absolute inset-0 size-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100"
                            >
                                <source src="{{ $preview->source() }}">
                            </video>
                        @endif

                        @if ($client->logoUrl())
                            <span class="absolute inset-x-[12%] top-[14%] flex h-[22%] items-center justify-center overflow-hidden rounded-2xl bg-white p-4 shadow-lg">
                                <img
                                    src="{{ $client->logoUrl() }}"
                                    alt="{{ $client->name }} logo"
                                    loading="lazy"
                                    decoding="async"
                                    class="size-full object-contain"
                                >
                            </span>
                        @endif

                        <span class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></span>

                        {{-- Editorial caption: index, sector, then the work itself --}}
                        <span class="absolute inset-x-0 bottom-0 flex flex-col gap-2 p-7 lg:p-9">
                            <span class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.28em] {{ $isEducational ? 'text-brand-mint' : 'text-brand-coral' }}">
                                <span class="tabular-nums">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="h-px w-6 bg-current opacity-40"></span>
                                <span>{{ $isEducational ? 'Education' : 'Enterprise' }}</span>
                            </span>

                            <span class="text-balance text-2xl font-bold leading-[1.1] tracking-tight text-white lg:text-3xl">
                                {{ $client->name }}
                            </span>

                            <span class="text-sm text-white/65">
                                {{ $client->project_type }}@if ($client->year) &middot; {{ $client->year }}@endif
                            </span>
                        </span>
                    </div>
                </article>
            @endforeach

            {{-- Closing plate keeps the last card from ending flush against the edge --}}
            <div class="w-[40vw] shrink-0 lg:w-[18vw]">
                <p class="text-sm leading-relaxed text-ink-900/45">
                    Keep scrolling for the full client wall.
                </p>
            </div>
        </div>
    </div>
</section>
