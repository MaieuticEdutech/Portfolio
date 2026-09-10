/**
 * Three.js is heavy (~133KB gzipped), so the bend demo is a dynamic import:
 * the chunk is only fetched on a page that actually contains the card, and
 * never downloaded by visitors to the portfolio itself.
 */
const boot = async () => {
    if (document.querySelector('[data-bend-demo]')) {
        const { initBendDemo } = await import('./bend-demo')
        await initBendDemo()
    }
}

boot()

document.addEventListener('livewire:navigated', boot)
