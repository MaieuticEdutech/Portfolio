@php
    $filters = [
        'all' => 'All work',
        'educational' => 'Education',
        'corporate' => 'Corporate',
    ];
@endphp

<div class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
    {{-- Filter bar --}}
    <div class="sticky top-0 z-20 -mx-4 mb-8 border-b border-white/5 bg-ink-900/85 px-4 py-4 backdrop-blur-lg sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-wrap gap-2" role="group" aria-label="Filter by sector">
                @foreach ($filters as $key => $label)
                    <button
                        type="button"
                        wire:click="filterBy('{{ $key }}')"
                        aria-pressed="{{ $category === $key ? 'true' : 'false' }}"
                        class="group flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition
                            {{ match (true) {
                                $category !== $key => 'border-white/10 bg-white/5 text-white/60 hover:border-white/25 hover:text-white',
                                $key === 'corporate' => 'border-brand-coral/60 bg-brand-coral/15 text-brand-coral shadow-[0_0_24px_-6px_rgba(248,132,126,0.6)]',
                                default => 'border-brand-mint/60 bg-brand-mint/15 text-brand-mint shadow-[0_0_24px_-6px_rgba(21,217,161,0.6)]',
                            } }}"
                    >
                        {{ $label }}
                        <span class="rounded-full px-1.5 py-0.5 text-xs tabular-nums {{ $category === $key ? ($key === 'corporate' ? 'bg-brand-coral/20' : 'bg-brand-mint/20') : 'bg-white/5 text-white/40' }}">
                            {{ $this->counts[$key] }}
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="relative ml-auto w-full sm:w-64">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-white/30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search clients"
                    aria-label="Search clients"
                    class="w-full rounded-full border border-white/10 bg-white/5 py-2 pl-9 pr-4 text-sm text-white placeholder-white/30 transition focus:border-brand-mint/50 focus:bg-white/10 focus:outline-none"
                >
            </div>
        </div>
    </div>

    {{-- Tile grid --}}
    @if ($this->clients->isEmpty())
        <div class="rounded-2xl border border-dashed border-white/10 py-24 text-center">
            <p class="text-lg text-white/70">No clients match &ldquo;{{ $search }}&rdquo;.</p>
            <button type="button" wire:click="$set('search', '')" class="mt-3 text-sm text-brand-mint hover:underline">
                Clear search
            </button>
        </div>
    @else
        {{-- grid-flow-dense backfills the gaps big tiles leave, so the wall reads as one solid mosaic --}}
        <div class="grid auto-rows-[7.5rem] grid-flow-dense grid-cols-2 gap-3 sm:auto-rows-[8.5rem] sm:grid-cols-4 sm:gap-4 lg:grid-cols-6">
            @foreach ($this->clients as $index => $client)
                @php
                    // Sector colour lives in the top-left corner and settles into the shared
                    // ink ground, so adjacent teal and red tiles meet dark-to-dark instead of
                    // clashing edge to edge.
                    $stops = $client->gradientStops();
                    $highlight = $stops[0] ?? '#0C1817';
                    $tone = $stops[count($stops) - 1] ?? $highlight;
                    $gradient = "radial-gradient(85% 70% at 0% 0%, {$highlight} 0%, transparent 62%), "
                        ."linear-gradient(150deg, color-mix(in oklab, {$tone} 62%, #0C1817) 0%, #0C1817 78%)";
                    $isBig = $client->tile_size === 'big';
                    $isMed = $client->tile_size === 'med';
                    $isEducational = $client->category === 'educational';
                    $span = match ($client->tile_size) {
                        'big' => 'col-span-2 row-span-2 rounded-3xl',
                        'med' => 'col-span-2 row-span-1 rounded-2xl',
                        default => 'col-span-1 row-span-1 rounded-2xl',
                    };
                    $initial = Str::upper(Str::substr($client->name, 0, 1));
                @endphp

                <button
                    type="button"
                    wire:key="client-{{ $client->id }}"
                    wire:click="select({{ $client->id }})"
                    style="animation-delay: {{ min($index, 24) * 25 }}ms"
                    class="animate-tile-in group relative isolate overflow-hidden text-left shadow-[inset_0_1px_0_rgba(255,255,255,0.18)] ring-1 ring-white/10 transition duration-300 hover:-translate-y-1.5 hover:ring-white/40 focus:outline-none focus-visible:ring-2 {{ $span }}
                        {{ $isEducational
                            ? 'hover:shadow-[0_28px_60px_-18px_rgba(21,217,161,0.55)] focus-visible:ring-brand-mint'
                            : 'hover:shadow-[0_28px_60px_-18px_rgba(248,132,126,0.55)] focus-visible:ring-brand-coral' }}"
                >
                    {{-- Layered surface: brand gradient, a soft light source, film grain, then a vignette for legibility --}}
                    <span class="absolute inset-0 -z-10 transition-transform duration-700 ease-out group-hover:scale-110" style="background: {{ $gradient }}"></span>
                    <span class="absolute inset-0 -z-10 bg-[radial-gradient(120%_90%_at_0%_0%,rgba(255,255,255,0.18),transparent_55%)] mix-blend-soft-light"></span>
                    <span class="tile-grain absolute inset-0 -z-10 opacity-40 mix-blend-overlay"></span>
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></span>
                    <span aria-hidden="true" class="absolute inset-0 -z-10 -translate-x-full skew-x-12 bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 ease-out group-hover:translate-x-full"></span>

                    {{-- Watermark monogram gives every tile its own signature while logos are pending --}}
                    @unless ($client->logoUrl())
                        <span aria-hidden="true" class="pointer-events-none absolute -bottom-3 -right-1 select-none font-bold leading-none tracking-tighter text-white/[0.08] transition duration-500 group-hover:-translate-y-2 group-hover:text-white/[0.14] {{ $isBig ? 'text-[9rem] sm:text-[13rem]' : ($isMed ? 'text-[6.5rem] sm:text-[8rem]' : 'text-[5.5rem] sm:text-[6.5rem]') }}">
                            {{ $initial }}
                        </span>
                    @endunless

                    {{-- Top rail: sector tag + film count --}}
                    <span class="absolute inset-x-3 top-3 flex items-center justify-between gap-2 sm:inset-x-4 sm:top-4">
                        <span class="flex items-center gap-1.5 rounded-full bg-black/30 py-1 pl-2 pr-2.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/85 ring-1 ring-white/10 backdrop-blur-sm">
                            <span class="size-1.5 rounded-full shadow-[0_0_8px_currentColor] {{ $isEducational ? 'bg-brand-mint text-brand-mint' : 'bg-brand-coral text-brand-coral' }}"></span>
                            <span class="{{ $isBig || $isMed ? '' : 'hidden sm:inline' }}">{{ $isEducational ? 'Education' : 'Corporate' }}</span>
                        </span>

                        <span class="flex items-center gap-1 rounded-full bg-black/30 px-2 py-1 text-[11px] font-medium tabular-nums text-white/85 ring-1 ring-white/10 backdrop-blur-sm">
                            <svg class="size-2.5" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M3 1.5v9l7.5-4.5L3 1.5Z" /></svg>
                            {{ $client->videos_count }}<span class="sr-only"> films</span>
                        </span>
                    </span>

                    {{-- Logo sits in the band between the top rail and the name, so it never collides with either --}}
                    <span class="relative flex size-full flex-col justify-end p-3 sm:p-4 {{ $isBig ? 'sm:p-6' : '' }}">
                        @if ($client->logoUrl())
                            <span class="flex min-h-0 flex-1 items-end pb-1.5 pt-7 {{ $isBig ? 'sm:pb-3 sm:pt-8' : '' }}">
                                {{-- Marks are flattened to white so dark wordmarks stay legible on the ink gradient --}}
                                <img
                                    src="{{ $client->logoUrl() }}"
                                    alt="{{ $client->name }} logo"
                                    loading="lazy"
                                    class="w-auto max-h-full object-contain object-left-bottom opacity-90 brightness-0 invert drop-shadow-[0_2px_8px_rgba(0,0,0,0.45)] transition duration-300 group-hover:opacity-100 {{ $isBig ? 'h-12 max-w-[70%] sm:h-16' : 'h-6 max-w-[75%] sm:h-8' }}"
                                >
                            </span>
                        @endif

                        <span class="text-balance font-bold leading-[1.05] tracking-tight text-white drop-shadow-md {{ $isBig ? 'text-2xl sm:text-4xl' : ($isMed ? 'text-base sm:text-xl' : 'text-sm sm:text-base') }}">
                            {{ $client->name }}
                        </span>

                        <span class="mt-1.5 flex items-center gap-2 text-[11px] text-white/70 transition duration-300 sm:text-xs {{ $isBig ? 'sm:mt-2 sm:text-sm' : 'translate-y-1 opacity-0 group-hover:translate-y-0 group-hover:opacity-100' }}">
                            <span class="truncate">{{ $client->project_type }}</span>
                            @if ($client->year)
                                <span aria-hidden="true" class="text-white/40">&middot;</span><span class="tabular-nums">{{ $client->year }}</span>
                            @endif
                            <span aria-hidden="true" class="ml-auto -translate-x-1 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100 {{ $isEducational ? 'text-brand-mint' : 'text-brand-coral' }}">&rarr;</span>
                        </span>
                    </span>
                </button>
            @endforeach
        </div>

        <p class="mt-10 flex items-center justify-center gap-3 text-xs uppercase tracking-[0.2em] text-white/35">
            <span class="h-px w-8 bg-white/10"></span>
            Showing {{ $this->clients->count() }} of {{ $this->counts['all'] }} clients
            <span class="h-px w-8 bg-white/10"></span>
        </p>
    @endif

    {{-- Client detail --}}
    @if ($client = $this->selectedClient)
        @php
            $stops = $client->gradientStops();
            $gradient = count($stops) > 1
                ? 'linear-gradient(135deg, '.implode(', ', $stops).')'
                : ($stops[0] ?? '#0C1817');
        @endphp

        <div
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/80 p-4 backdrop-blur-sm sm:p-8"
            role="dialog"
            aria-modal="true"
            aria-labelledby="client-detail-title"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            x-on:click.self="$wire.close()"
        >
            <div class="my-auto w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-ink-800 shadow-2xl">
                <div class="relative px-6 py-8 sm:px-8" style="background: {{ $gradient }}">
                    <div class="absolute inset-0 bg-black/35"></div>

                    <button
                        type="button"
                        wire:click="close"
                        aria-label="Close"
                        class="absolute right-4 top-4 grid size-9 place-items-center rounded-full bg-black/40 text-white/80 transition hover:bg-black/60 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
                            {{ $client->category === 'educational' ? 'Education' : 'Corporate' }}
                        </p>
                        <h2 id="client-detail-title" class="mt-2 text-3xl font-bold text-white sm:text-4xl">{{ $client->name }}</h2>
                        <p class="mt-2 text-sm text-white/80">
                            {{ $client->project_type }}@if ($client->year) &middot; {{ $client->year }}@endif
                        </p>
                    </div>
                </div>

                <div class="space-y-4 p-6 sm:p-8">
                    @forelse ($client->videos as $video)
                        <div class="overflow-hidden rounded-2xl border border-white/10 bg-ink-700">
                            @if ($video->isUploaded())
                                <video controls preload="none" class="aspect-video w-full bg-black" @if ($video->thumbnailUrl()) poster="{{ $video->thumbnailUrl() }}" @endif>
                                    <source src="{{ $video->source() }}">
                                </video>
                            @elseif ($embed = $video->embedUrl())
                                <iframe src="{{ $embed }}" title="{{ $video->title }}" loading="lazy" allowfullscreen class="aspect-video w-full border-0 bg-black"></iframe>
                            @elseif ($video->source())
                                <a href="{{ $video->source() }}" target="_blank" rel="noopener noreferrer" class="flex aspect-video w-full items-center justify-center bg-black/60 text-sm text-brand-mint hover:underline">
                                    Watch on external site &rarr;
                                </a>
                            @else
                                <div class="flex aspect-video w-full flex-col items-center justify-center gap-2 bg-ink-800 text-white/40">
                                    <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="2" y="5" width="14" height="14" rx="3" /><path d="m16 12 6-3.5v7L16 12Z" stroke-linejoin="round" />
                                    </svg>
                                    <span class="text-xs uppercase tracking-widest">Footage coming soon</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <p class="truncate text-sm font-medium text-white/90">{{ $video->title }}</p>
                                @if ($video->duration_label)
                                    <span class="shrink-0 text-xs tabular-nums text-white/40">{{ $video->duration_label }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-white/50">No films published for this client yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>