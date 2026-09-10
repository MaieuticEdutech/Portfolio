@props(['variant' => 'camera', 'flip' => false])

{{--
    Hand-drawn crew figures, rigged rather than drawn as a picture: each limb is
    its own group with a transform-origin at the joint, so the CSS rotates real
    shoulders and elbows on a loop. Stroke-only, so they scale to any size and
    inherit colour from the page.
--}}
<svg
    viewBox="0 0 120 170"
    fill="none"
    stroke="currentColor"
    stroke-width="2.75"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'figure-'.$variant.($flip ? ' -scale-x-100' : '')]) }}
>
    <g class="figure-bob">
        {{-- Head, with the slight wobble of a drawn line rather than a perfect circle --}}
        <path d="M60 8c11.6 0 20.5 8.4 20.5 19S71.9 46.5 60 46.5 39.5 37.6 39.5 27 48.4 8 60 8Z" />
        <path d="M52.5 24.5v2.2M67.5 24.5v2.2" stroke-width="3.4" />
        <path d="M52 34.5c2.6 3.2 5.3 4.7 8 4.7s5.4-1.5 8-4.7" />

        {{-- Torso tapers the way the reference does, wider at the shoulders --}}
        <path d="M60 46.5v50" />
        <path d="M46 60c4.6-2.6 9.3-3.9 14-3.9s9.4 1.3 14 3.9" />

        {{-- Legs --}}
        <g class="figure-leg-back"><path d="M60 96 48 130l-5 12" /></g>
        <g class="figure-leg-front"><path d="m60 96 12 34 5 12" /></g>
        <path class="figure-foot-back" d="M43 142h9" />
        <path class="figure-foot-front" d="M77 142h9" />

        @if ($variant === 'camera')
            {{-- Back arm braces the rig; front arm pans it across the shot --}}
            <g class="figure-arm-back"><path d="M60 58 41 74l-3 11" /></g>
            <g class="figure-arm-front">
                <path d="m60 58 20 10" />
                <g class="figure-prop-camera">
                    <rect x="76" y="52" width="30" height="20" rx="4" />
                    <path d="m106 58 10-5v14l-10-5Z" />
                    <circle cx="88" cy="62" r="5" />
                </g>
            </g>
        @elseif ($variant === 'clapper')
            <g class="figure-arm-back"><path d="M60 58 40 70l-2 12" /></g>
            <g class="figure-arm-front">
                <path d="m60 58 22 6" />
                <g class="figure-prop-clapper">
                    <rect x="80" y="60" width="30" height="20" rx="3" />
                    <g class="figure-clapper-arm">
                        <path d="M80 60h30" stroke-width="4" />
                        <path d="M86 60v-5M96 60v-5M106 60v-5" stroke-width="2.2" />
                    </g>
                </g>
            </g>
        @else
            {{-- Boom operator: both arms up, pole swaying overhead --}}
            <g class="figure-arm-back"><path d="M60 58 44 40l-2-10" /></g>
            <g class="figure-arm-front"><path d="m60 58 18-16 3-10" /></g>
            <g class="figure-prop-boom">
                <path d="M30 24h62" stroke-width="3" />
                <path d="M30 24c-5 0-9 3-9 7s4 7 9 7 9-3 9-7-4-7-9-7Z" />
            </g>
        @endif
    </g>

    {{-- The ground line the reference figures stand on --}}
    <path d="M34 148h52" stroke-width="2" opacity="0.35" />
</svg>
