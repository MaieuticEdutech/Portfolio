<x-layouts::app title="Bend demo">
    <div data-bend-demo class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-teal">Interaction lab</p>
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-ink-900 sm:text-5xl">Cursor-responsive sheet bend</h1>
        <p class="mt-4 max-w-2xl text-ink-900/60">
            One isolated card. The whole artwork &mdash; gradient, logo plate and caption &mdash; is a
            single WebGL texture on a subdivided plane, so the vertex shader deforms the geometry
            itself. The silhouette changes; nothing here is a CSS transform.
        </p>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div>
                <div
                    data-bend-stage
                    data-highlight="#15D9A1"
                    data-tone="#00615C"
                    data-accent="#15D9A1"
                    data-index="01"
                    data-sector="Education"
                    data-title="{{ $client?->name ?? 'REVA University Online' }}"
                    data-meta="{{ $client?->project_type ?? 'Program explainer' }} · {{ $client?->year ?? '2024' }}"
                    data-logo="{{ $client?->logoUrl() }}"
                    class="relative aspect-[4/5] w-full max-w-xl cursor-crosshair"
                ></div>

                <p data-bend-readout class="mt-5 text-sm text-ink-900/55">
                    Move the cursor across the card, or step through the states on the right.
                </p>
            </div>

            <div class="space-y-8">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-ink-900/45">States</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([
                            'top-left' => 'Top left',
                            'top-right' => 'Top right',
                            'centre' => 'Centre',
                            'bottom-left' => 'Bottom left',
                            'bottom-right' => 'Bottom right',
                            'leave' => 'Mouse leave',
                        ] as $state => $label)
                            <button
                                type="button"
                                data-bend-state="{{ $state }}"
                                class="rounded-lg border border-ink-900/12 bg-white px-3 py-2 text-sm text-ink-900/70 transition hover:border-brand-teal/50 hover:text-brand-teal {{ $state === 'leave' ? 'col-span-2' : '' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-ink-900/45">Tuning</p>
                    <div class="space-y-4">
                        @foreach ([
                            ['bendDepth', 'Bend depth', 0, 1, 0.01],
                            ['bendRadius', 'Bend radius', 0.1, 1, 0.01],
                            ['bendFalloff', 'Falloff', 0.5, 4, 0.1],
                            ['uvPull', 'Artwork pull', 0, 0.2, 0.005],
                        ] as [$key, $label, $min, $max, $step])
                            <label class="block">
                                <span class="flex items-center justify-between text-sm text-ink-900/60">
                                    {{ $label }}
                                    <span data-bend-value="{{ $key }}" class="tabular-nums text-ink-900/40"></span>
                                </span>
                                <input
                                    type="range"
                                    data-bend-tune="{{ $key }}"
                                    min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                                    class="mt-2 w-full accent-brand-teal"
                                >
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-ink-900/45">
                        Dial these in here, then copy the values into <code>CONFIG</code> at the top of
                        <code>resources/js/bend-card.js</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
