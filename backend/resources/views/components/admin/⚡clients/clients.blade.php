<div>
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Clients</h1>
            <p class="mt-1 text-sm text-white/40">Pick a client to manage its films.</p>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-3">
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search clients"
                aria-label="Search clients"
                class="w-52 rounded-lg border border-white/10 bg-ink-800 px-3 py-2 text-sm text-white placeholder-white/25 focus:border-brand-mint/60 focus:outline-none"
            >

            <select
                wire:model.live="category"
                aria-label="Filter by sector"
                class="rounded-lg border border-white/10 bg-ink-800 px-3 py-2 text-sm text-white focus:border-brand-mint/60 focus:outline-none"
            >
                <option value="all">All sectors</option>
                <option value="educational">Education</option>
                <option value="corporate">Corporate</option>
            </select>

            <label class="flex items-center gap-2 text-sm text-white/50">
                <input wire:model.live="onlyIncomplete" type="checkbox" class="rounded border-white/20 bg-ink-800 text-brand-teal focus:ring-brand-mint/40">
                Needs assets
            </label>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-white/10">
        <table class="w-full text-left text-sm">
            <thead class="bg-ink-800 text-xs uppercase tracking-wider text-white/40">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">Client</th>
                    <th scope="col" class="px-4 py-3 font-medium">Sector</th>
                    <th scope="col" class="px-4 py-3 font-medium">Logo</th>
                    <th scope="col" class="px-4 py-3 font-medium">Films</th>
                    <th scope="col" class="px-4 py-3 font-medium">Live</th>
                    <th scope="col" class="px-4 py-3 text-right font-medium">
                        <span class="sr-only">Manage</span>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-white/5">
                @forelse ($this->clients as $client)
                    <tr wire:key="client-{{ $client->id }}" class="bg-ink-900/60 transition hover:bg-ink-800/60">
                        <td class="px-4 py-3">
                            <p class="font-medium text-white">{{ $client->name }}</p>
                            <p class="text-xs text-white/35">{{ $client->project_type }}@if ($client->year) &middot; {{ $client->year }}@endif</p>
                        </td>

                        <td class="px-4 py-3 text-white/50">
                            {{ $client->category === 'educational' ? 'Education' : 'Corporate' }}
                        </td>

                        <td class="px-4 py-3">
                            @if ($client->logo_path)
                                <span class="text-brand-mint">Uploaded</span>
                            @else
                                <span class="text-white/30">Missing</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 tabular-nums">
                            <span class="{{ $client->ready_videos_count > 0 ? 'text-white' : 'text-white/30' }}">
                                {{ $client->ready_videos_count }}
                            </span>
                            <span class="text-white/30">/ {{ $client->videos_count }} ready</span>
                        </td>

                        <td class="px-4 py-3">
                            <button
                                type="button"
                                wire:click="togglePublished({{ $client->id }})"
                                aria-pressed="{{ $client->is_published ? 'true' : 'false' }}"
                                class="rounded-full px-2.5 py-1 text-xs font-medium transition {{ $client->is_published ? 'bg-brand-teal/25 text-brand-mint hover:bg-brand-teal/40' : 'bg-white/5 text-white/40 hover:bg-white/10' }}"
                            >
                                {{ $client->is_published ? 'Live' : 'Hidden' }}
                            </button>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('admin.clients.films', $client) }}"
                                wire:navigate
                                class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-white/70 transition hover:border-brand-mint/50 hover:text-brand-mint"
                            >
                                Manage films
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="bg-ink-900/60 px-4 py-12 text-center text-white/40">
                            No clients match those filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->clients->links() }}
    </div>
</div>
