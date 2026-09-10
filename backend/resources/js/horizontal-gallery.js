import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

/**
 * Vertical scroll drives horizontal travel.
 *
 * The section is deliberately tall. While it is pinned, scroll progress is
 * mapped onto the track's translateX, so the page appears to move sideways
 * without the browser ever scrolling sideways. The distance is measured from
 * the DOM rather than hardcoded, so card size, gap and count can all change
 * without touching this file.
 *
 *   section (tall)
 *     └── viewport (pinned, overflow hidden)
 *           └── track (flex, width: max-content)  ← translated
 */
export function initHorizontalGallery() {
    const sections = document.querySelectorAll('[data-hgallery]')
    if (!sections.length) return

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)')

    sections.forEach((section) => {
        const viewport = section.querySelector('[data-hgallery-viewport]')
        const track = section.querySelector('[data-hgallery-track]')
        if (!viewport || !track) return

        let ctx

        const build = () => {
            ctx?.revert()

            // Reduced motion: no pin, no scrub. The track becomes an ordinary
            // horizontally scrollable strip the user drives themselves, which
            // keeps keyboard and touch behaviour intact.
            if (reduced.matches) {
                viewport.classList.add('overflow-x-auto')
                viewport.classList.remove('overflow-hidden')
                section.style.height = ''
                return
            }

            viewport.classList.remove('overflow-x-auto')
            viewport.classList.add('overflow-hidden')

            ctx = gsap.context(() => {
                // How far the track has to travel for its right edge to meet
                // the viewport's right edge. Measured, never assumed.
                const distance = () => Math.max(0, track.scrollWidth - viewport.clientWidth)

                // Scroll length is tied to travel distance, so the pacing stays
                // constant whether there are four cards or forty.
                const scrollLength = () => distance() + window.innerHeight * 0.5

                const travel = gsap.to(track, {
                    x: () => -distance(),
                    ease: 'none',
                    scrollTrigger: {
                        trigger: section,
                        start: 'top top',
                        end: () => `+=${scrollLength()}`,
                        pin: viewport,
                        pinSpacing: true,
                        scrub: 1, // a beat of lag; cinematic rather than mechanical
                        invalidateOnRefresh: true,
                        anticipatePin: 1,
                    },
                })

                // Cards settle as they arrive. containerAnimation is what lets a
                // trigger measure against horizontal travel instead of page scroll,
                // so it has to be the tween itself, not a lookup by id.
                gsap.utils.toArray('[data-hgallery-card]', track).forEach((card) => {
                    gsap.fromTo(
                        card,
                        { opacity: 0.5, scale: 0.96 },
                        {
                            opacity: 1,
                            scale: 1,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: card,
                                containerAnimation: travel,
                                start: 'left 92%',
                                end: 'left 45%',
                                scrub: true,
                            },
                        }
                    )
                })
            }, section)
        }

        build()

        // Recalculate on resize and on a reduced-motion preference change.
        const onResize = gsap.utils.debounce(() => {
            ScrollTrigger.refresh()
        }, 200)

        window.addEventListener('resize', onResize)
        reduced.addEventListener?.('change', build)

        // Livewire swaps DOM on navigate; tear everything down cleanly.
        document.addEventListener('livewire:navigating', () => {
            ctx?.revert()
            window.removeEventListener('resize', onResize)
            reduced.removeEventListener?.('change', build)
        }, { once: true })
    })
}
