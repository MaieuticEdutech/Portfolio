<div>
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <a href="{{ route('admin.clients') }}" wire:navigate class="text-sm text-white/40 transition hover:text-white">
                &larr; All clients
            </a>
            <h1 class="mt-2 text-2xl font-bold text-white">{{ $client->name }}</h1>
            <p class="mt-1 text-sm text-white/40">
                {{ $client->project_type }}@if ($client->year) &middot; {{ $client->year }}@endif
            </p>
        </div>

        <button
            type="button"
            wire:click="newFilm"
            class="ml-auto rounded-lg bg-brand-teal px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-mint hover:text-ink-900"
        >
            Add a film
        </button>
    </div>

    {{-- Editor --}}
    @if ($editingId !== null)
        <form wire:submit="save" class="mt-6 space-y-4 rounded-2xl border border-brand-teal/30 bg-ink-800 p-6">
            <p class="text-sm font-semibold text-white">
                {{ $editingId ? 'Edit film' : 'New film' }}
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="title" class="mb-1.5 block text-sm text-white/60">Title</label>
                    <input wire:model="title" id="title" type="text"
                           class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white focus:border-brand-mint/60 focus:outline-none">
                    @error('title') <p class="mt-1 text-sm text-brand-coral">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="durationLabel" class="mb-1.5 block text-sm text-white/60">Duration <span class="text-white/25">(e.g. 2:14)</span></label>
                    <input wire:model="durationLabel" id="durationLabel" type="text"
                           class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white focus:border-brand-mint/60 focus:outline-none">
                    @error('durationLabel') <p class="mt-1 text-sm text-brand-coral">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="videoFile" class="mb-1.5 block text-sm text-white/60">Upload the film</label>
                <input wire:model="videoFile" id="videoFile" type="file" accept="video/mp4,video/quicktime,video/webm"
                       class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white/70 file:mr-3 file:rounded file:border-0 file:bg-white/10 file:px-3 file:py-1 file:text-white">
                @error('videoFile') <p class="mt-1 text-sm text-brand-coral">{{ $message }}</p> @enderror

                <div wire:loading wire:target="videoFile" class="mt-2 text-sm text-brand-mint">Uploading&hellip;</div>

                <p class="mt-1.5 text-xs text-white/30">MP4, MOV or WebM, up to 2GB. An upload replaces whatever is on the slot now.</p>
            </div>

            <div>
                <label for="videoUrl" class="mb-1.5 block text-sm text-white/60">
                    &hellip;or paste a link <span class="text-white/25">(YouTube or Vimeo)</span>
                </label>
                <input wire:model="videoUrl" id="videoUrl" type="url" placeholder="https://vimeo.com/..."
                       class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white placeholder-white/20 focus:border-brand-mint/60 focus:outline-none">
                @error('videoUrl') <p class="mt-1 text-sm text-brand-coral">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-white/30">An uploaded file always wins over a pasted link.</p>
            </div>

            <div>
                <label for="thumbnailFile" class="mb-1.5 block text-sm text-white/60">Poster image <span class="text-white/25">(optional)</span></label>
                <input wire:model="thumbnailFile" id="thumbnailFile" type="file" accept="image/*"
                       class="w-full rounded-lg border border-white/10 bg-ink-700 px-3 py-2 text-sm text-white/70 file:mr-3 file:rounded file:border-0 file:bg-white/10 file:px-3 file:py-1 file:text-white">
                @error('thumbnailFile') <p class="mt-1 text-sm text-brand-coral">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="save,videoFile,thumbnailFile"
                        class="rounded-lg bg-brand-teal px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-mint hover:text-ink-900 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Save film</span>
                    <span wire:loading wire:target="save">Saving&hellip;</span>
                </button>

                <button type="button" wire:click="cancel" class="rounded-lg px-4 py-2 text-sm text-white/50 transition hover:text-white">
                    Cancel
                </button>
            </div>
        </form>
    @endif

    {{-- Film list --}}
    <div class="mt-6 space-y-3">
        @forelse ($client->videos as $video)
            <div wire:key="film-{{ $video->id }}" class="flex flex-wrap items-center gap-4 rounded-2xl border border-white/10 bg-ink-800 p-4">
                <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-lg bg-ink-700">
                    @if ($video->thumbnailUrl())
                        <img src="{{ $video->thumbnailUrl() }}" alt="" class="size-full object-cover">
                    @else
                        <svg class="size-5 text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="2" y="5" width="14" height="14" rx="3" /><path d="m16 12 6-3.5v7L16 12Z" stroke-linejoin="round" />
                        </svg>
                    @endif
                </div>

                <div class="min-w-48 flex-1">
                    <p class="font-medium text-white">{{ $video->title }}</p>
                    <p class="mt-0.5 text-xs">
                        @if ($video->isUploaded())
                            <span class="text-brand-mint">Uploaded file</span>
                        @elseif ($video->video_url)
                            <span class="text-brand-mint">Linked</span>
                            <span class="text-white/30">&middot; {{ Str::limit($video->video_url, 40) }}</span>
                        @else
                            <span class="text-white/30">Awaiting footage</span>
                        @endif
                        @if ($video->duration_label)
                            <span class="text-white/30">&middot; {{ $video->duration_label }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" wire:click="moveUp({{ $video->id }})" aria-label="Move {{ $video->title }} up"
                            class="grid size-8 place-items-center rounded text-white/30 transition hover:bg-white/5 hover:text-white">&uarr;</button>
                    <button type="button" wire:click="moveDown({{ $video->id }})" aria-label="Move {{ $video->title }} down"
                            class="grid size-8 place-items-center rounded text-white/30 transition hover:bg-white/5 hover:text-white">&darr;</button>

                    <button type="button" wire:click="edit({{ $video->id }})"
                            class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-white/70 transition hover:border-brand-mint/50 hover:text-brand-mint">
                        Edit
                    </button>

                    @if (! $video->isPending())
                        <button type="button" wire:click="removeFootage({{ $video->id }})"
                                wire:confirm="Remove the footage from this slot? The film row stays."
                                class="rounded-lg px-3 py-1.5 text-xs text-white/40 transition hover:text-white">
                            Clear
                        </button>
                    @endif

                    <button type="button" wire:click="deleteFilm({{ $video->id }})"
                            wire:confirm="Delete this film for good? This also removes the uploaded file."
                            class="rounded-lg px-3 py-1.5 text-xs text-white/40 transition hover:text-brand-coral">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <p class="rounded-2xl border border-dashed border-white/10 py-12 text-center text-sm text-white/40">
                No films on this client yet.
            </p>
        @endforelse
    </div>
</div>
