import { initHorizontalGallery } from './horizontal-gallery'

initHorizontalGallery()

// Livewire replaces the DOM on navigate, so the gallery is rebuilt after it.
document.addEventListener('livewire:navigated', initHorizontalGallery)
