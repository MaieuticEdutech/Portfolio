import { BendCard, CONFIG } from './bend-card'
import { drawCardArtwork } from './card-artwork'

/**
 * The isolated demo: one card, plus buttons that drive the cursor to fixed
 * positions so the deformation can be checked deliberately rather than by
 * waggling a mouse and hoping.
 */
export async function initBendDemo() {
    const host = document.querySelector('[data-bend-demo]')
    if (!host) return

    const stage = host.querySelector('[data-bend-stage]')
    const readout = host.querySelector('[data-bend-readout]')
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)')

    const artwork = await drawCardArtwork({
        highlight: stage.dataset.highlight,
        tone: stage.dataset.tone,
        index: stage.dataset.index,
        sector: stage.dataset.sector,
        title: stage.dataset.title,
        meta: stage.dataset.meta,
        accent: stage.dataset.accent,
        logoUrl: stage.dataset.logo || null,
    })

    if (reduced.matches) {
        // No shader at all: show the flat artwork so the card stays usable.
        const img = new Image()
        img.src = artwork.toDataURL()
        img.alt = stage.dataset.title
        img.className = 'size-full object-contain'
        stage.appendChild(img)
        readout.textContent = 'Reduced motion is on — showing the card flat, no deformation.'
        return
    }

    const card = new BendCard(stage, { artwork })

    // Named states so the bend can be inspected one position at a time.
    const STATES = {
        'top-left': [0.18, 0.82],
        'top-right': [0.82, 0.82],
        centre: [0.5, 0.5],
        'bottom-left': [0.18, 0.18],
        'bottom-right': [0.82, 0.18],
    }

    host.querySelectorAll('[data-bend-state]').forEach((button) => {
        button.addEventListener('click', () => {
            const name = button.dataset.bendState

            if (name === 'leave') {
                card.hoverTarget = 0
                card.start()
                readout.textContent = 'Mouse leave — relaxing back to the original shape.'
                return
            }

            const [x, y] = STATES[name]
            card.target.set(x, y)
            card.hoverTarget = 1
            card.start()
            readout.textContent = `Cursor at ${name} (${x}, ${y}) — sheet bends toward it, edges follow.`
        })
    })

    // Live tuning, so the values in CONFIG can be dialled in against the real card.
    host.querySelectorAll('[data-bend-tune]').forEach((input) => {
        const key = input.dataset.bendTune
        input.value = CONFIG[key]
        const label = host.querySelector(`[data-bend-value="${key}"]`)
        if (label) label.textContent = Number(CONFIG[key]).toFixed(3)

        input.addEventListener('input', () => {
            const value = parseFloat(input.value)
            card.cfg[key] = value
            const uniform = {
                bendDepth: 'uDepth', bendRadius: 'uRadius',
                bendFalloff: 'uFalloff', uvPull: 'uPull',
            }[key]
            if (uniform) card.material.uniforms[uniform].value = value
            if (label) label.textContent = value.toFixed(3)
            card.start()
        })
    })

    window.addEventListener('resize', () => card.resize())
}
