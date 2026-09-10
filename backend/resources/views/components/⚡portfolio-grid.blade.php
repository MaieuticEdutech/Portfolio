<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'filter', except: 'all')]
    public string $category = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $selectedClientId = null;

    public function updatedCategory(): void
    {
        $this->selectedClientId = null;
    }

    public function updatedSearch(): void
    {
        $this->selectedClientId = null;
    }

    public function filterBy(string $category): void
    {
        $this->category = in_array($category, PortfolioClient::CATEGORIES, true) ? $category : 'all';
        $this->selectedClientId = null;
    }

    public function select(int $clientId): void
    {
        $this->selectedClientId = $clientId;
    }

    public function close(): void
    {
        $this->selectedClientId = null;
    }

    #[Computed]
    public function clients(): Collection
    {
        return PortfolioClient::query()
            ->published()
            ->category($this->category)
            ->when(filled($this->search), fn ($q) => $q->where('name', 'like', '%'.trim($this->search).'%'))
            ->withCount('videos')
            ->inGridOrder()
            ->get();
    }

    /**
     * Drives the counts on the filter pills — unaffected by the active filter.
     */
    #[Computed]
    public function counts(): array
    {
        $byCategory = PortfolioClient::query()
            ->published()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return [
            'all' => (int) $byCategory->sum(),
            'educational' => (int) $byCategory->get('educational', 0),
            'corporate' => (int) $byCategory->get('corporate', 0),
        ];
    }

    #[Computed]
    public function selectedClient(): ?PortfolioClient
    {
        if (! $this->selectedClientId) {
            return null;
        }

        return PortfolioClient::query()
            ->published()
            ->with('videos')
            ->find($this->selectedClientId);
    }
};
?>

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
                            {{ $category === $key
                                ? 'border-brand-mint/60 bg-brand-mint/15 text-brand-mint'
                                : 'border-white/10 bg-white/5 text-white/60 hover:border-white/25 hover:text-white' }}"
                    >
                        {{ $label }}
                        <span class="rounded-full px-1.5 py-0.5 text-xs tabular-nums {{ $category === $key ? 'bg-brand-mint/20' : 'bg-white/5 text-white/40' }}">
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
        <div class="grid auto-rows-[7rem] grid-cols-2 gap-3 sm:auto-rows-[8rem] sm:grid-cols-4 lg:grid-cols-6">
            @foreach ($this->clients as $index => $client)
                @php
                    $stops = $client->gradientStops();
                    $gradient = count($stops) > 1
                        ? 'linear-gradient(135deg, '.implode(', ', $stops).')'
                        : ($stops[0] ?? '#0C1817');
                    $span = match ($client->tile_size) {
                        'big' => 'col-span-2 row-span-2',
                        'med' => 'col-span-2 row-span-1',
                        default => 'col-span-1 row-span-1',
                    };
                @endphp

                <button
                    type="button"
                    wire:key="client-{{ $client->id }}"
                    wire:click="select({{ $client->id }})"
                    style="animation-delay: {{ min($index, 24) * 25 }}ms"
                    class="animate-tile-in group relative isolate overflow-hidden rounded-2xl text-left ring-1 ring-white/10 transition duration-300 hover:-translate-y-1 hover:ring-white/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-mint {{ $span }}"
                >
                    <span class="absolute inset-0 -z-10 transition-transform duration-500 group-hover:scale-105" style="background: {{ $gradient }}"></span>
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></span>

                    @if ($client->logoUrl())
                        <img src="{{ $client->logoUrl() }}" alt="{{ $client->name }} logo" class="absolute inset-0 -z-10 size-full object-contain p-6 opacity-90 mix-blend-luminosity">
                    @endif

                    <span class="relative flex size-full flex-col justify-end p-3 sm:p-4">
                        <span class="text-balance font-semibold leading-tight text-white drop-shadow {{ $client->tile_size === 'big' ? 'text-lg sm:text-2xl' : ($client->tile_size === 'med' ? 'text-sm sm:text-base' : 'text-xs sm:text-sm') }}">
                            {{ $client->name }}
                        </span>

                        <span class="mt-1 flex items-center gap-2 text-[11px] text-white/70 opacity-0 transition-opacity duration-300 group-hover:opacity-100 sm:text-xs">
                            <span class="truncate">{{ $client->project_type }}</span>
                            @if ($client->year)
                                <span aria-hidden="true">&middot;</span><span>{{ $client->year }}</span>
                            @endif
                        </span>
                    </span>

                    <span class="absolute right-3 top-3 rounded-full bg-black/40 px-2 py-0.5 text-[11px] font-medium text-white/80 backdrop-blur-sm">
                        {{ $client->videos_count }}
                    </span>
                </button>
            @endforeach
        </div>

        <p class="mt-8 text-center text-sm text-white/35">
            Showing {{ $this->clients->count() }} of {{ $this->counts['all'] }} clients
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
