import {
    CanvasTexture, Mesh, PerspectiveCamera, PlaneGeometry,
    Scene, ShaderMaterial, SRGBColorSpace, Vector2, WebGLRenderer,
} from 'three'

/**
 * Cursor-responsive sheet bending.
 *
 * The card is drawn once to a 2D canvas (gradient, logo, caption) and used as a
 * texture on a subdivided plane. The VERTEX shader displaces that geometry near
 * the cursor, which is what makes the silhouette itself change - UV distortion
 * alone would warp the picture but leave the rectangle intact. The fragment
 * shader then adds a small UV pull so lines inside the artwork curve with it.
 */

export const CONFIG = {
    bendDepth: 0.34,      // how far the sheet pushes toward the viewer
    bendRadius: 0.55,     // how much of the surface the cursor influences (0-1 in UV)
    bendFalloff: 1.6,     // higher = tighter, more local bend
    uvPull: 0.045,        // how much the artwork itself smears toward the cursor
    smoothing: 0.09,      // cursor easing per frame
    hoverEase: 0.06,      // hover strength easing per frame
    segments: 60,         // plane subdivisions; more = smoother silhouette
}

const VERT = /* glsl */ `
    uniform vec2  uMouse;
    uniform float uHover;
    uniform float uDepth;
    uniform float uRadius;
    uniform float uFalloff;

    varying vec2  vUv;
    varying float vInfluence;

    void main() {
        vUv = uv;

        // Falls off with distance from the cursor, so the bend stays local
        // instead of tilting the whole card.
        float d = distance(uv, uMouse);
        float influence = 1.0 - smoothstep(0.0, uRadius, d);
        influence = pow(influence, uFalloff) * uHover;
        vInfluence = influence;

        vec3 pos = position;

        // Push the sheet toward the viewer under the cursor.
        pos.z += influence * uDepth;

        // Draw the surrounding material in slightly, the way a real sheet
        // gathers when you press it.
        vec2 toCursor = uv - uMouse;
        float len = max(length(toCursor), 0.0001);
        pos.xy -= (toCursor / len) * influence * uDepth * 0.22;

        gl_Position = projectionMatrix * modelViewMatrix * vec4(pos, 1.0);
    }
`

const FRAG = /* glsl */ `
    uniform sampler2D uTexture;
    uniform vec2  uMouse;
    uniform float uPull;

    varying vec2  vUv;
    varying float vInfluence;

    void main() {
        // Smear the artwork toward the cursor so lines inside it curve too.
        vec2 uv = vUv - (vUv - uMouse) * vInfluence * uPull;

        vec4 texel = texture2D(uTexture, uv);

        // A touch of lift where the sheet faces the viewer, so the bend reads
        // as form rather than as a flat slide.
        texel.rgb += vInfluence * 0.06;

        gl_FragColor = texel;
    }
`

export class BendCard {
    constructor(el, { artwork, config = {} } = {}) {
        this.el = el
        this.cfg = { ...CONFIG, ...config }
        this.artwork = artwork

        this.mouse = new Vector2(0.5, 0.5)
        this.target = new Vector2(0.5, 0.5)
        this.hover = 0
        this.hoverTarget = 0
        this.raf = null
        this.running = false

        this.init()
    }

    init() {
        const { clientWidth: w, clientHeight: h } = this.el
        if (!w || !h) return

        this.renderer = new WebGLRenderer({ alpha: true, antialias: true })
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
        this.renderer.setSize(w, h)
        this.renderer.domElement.classList.add('size-full', 'block')
        this.el.appendChild(this.renderer.domElement)

        this.scene = new Scene()

        // A modest FOV keeps the perspective honest: enough that the bend has
        // depth, not so much that the card looks like it is being thrown.
        this.camera = new PerspectiveCamera(35, w / h, 0.1, 100)
        this.camera.position.z = 3.2

        const height = 2 * Math.tan((35 * Math.PI) / 360) * this.camera.position.z
        const width = height * (w / h)

        this.texture = new CanvasTexture(this.artwork)
        this.texture.colorSpace = SRGBColorSpace

        this.material = new ShaderMaterial({
            vertexShader: VERT,
            fragmentShader: FRAG,
            transparent: true,
            uniforms: {
                uTexture: { value: this.texture },
                uMouse: { value: new Vector2(0.5, 0.5) },
                uHover: { value: 0 },
                uDepth: { value: this.cfg.bendDepth },
                uRadius: { value: this.cfg.bendRadius },
                uFalloff: { value: this.cfg.bendFalloff },
                uPull: { value: this.cfg.uvPull },
            },
        })

        this.mesh = new Mesh(
            new PlaneGeometry(width, height, this.cfg.segments, this.cfg.segments),
            this.material
        )
        this.scene.add(this.mesh)

        this.bindEvents()
        this.renderer.render(this.scene, this.camera)
    }

    bindEvents() {
        // Pointer events cover mouse and pen; touch is excluded deliberately,
        // since there is no hover to respond to on a finger.
        this.onMove = (e) => {
            if (e.pointerType === 'touch') return
            const r = this.el.getBoundingClientRect()
            this.target.set((e.clientX - r.left) / r.width, 1 - (e.clientY - r.top) / r.height)
            this.hoverTarget = 1
            this.start()
        }

        this.onLeave = () => {
            this.hoverTarget = 0
            this.start()
        }

        this.el.addEventListener('pointermove', this.onMove)
        this.el.addEventListener('pointerleave', this.onLeave)
        this.el.addEventListener('pointercancel', this.onLeave)
    }

    start() {
        if (this.running) return
        this.running = true
        this.tick()
    }

    tick = () => {
        // Interpolate rather than snap, so the surface feels pushed rather
        // than teleported.
        this.mouse.lerp(this.target, this.cfg.smoothing)
        this.hover += (this.hoverTarget - this.hover) * this.cfg.hoverEase

        this.material.uniforms.uMouse.value.copy(this.mouse)
        this.material.uniforms.uHover.value = this.hover
        this.renderer.render(this.scene, this.camera)

        // Stop the loop once the sheet has settled; an idle card costs nothing.
        const settled = this.hover < 0.001 && this.hoverTarget === 0
        if (settled) {
            this.running = false
            this.raf = null
            return
        }

        this.raf = requestAnimationFrame(this.tick)
    }

    resize() {
        const { clientWidth: w, clientHeight: h } = this.el
        if (!w || !h) return
        this.renderer.setSize(w, h)
        this.camera.aspect = w / h
        this.camera.updateProjectionMatrix()
        this.renderer.render(this.scene, this.camera)
    }

    refreshArtwork() {
        this.texture.needsUpdate = true
        this.renderer.render(this.scene, this.camera)
    }

    destroy() {
        if (this.raf) cancelAnimationFrame(this.raf)
        this.el.removeEventListener('pointermove', this.onMove)
        this.el.removeEventListener('pointerleave', this.onLeave)
        this.el.removeEventListener('pointercancel', this.onLeave)
        this.mesh?.geometry.dispose()
        this.material?.dispose()
        this.texture?.dispose()
        this.renderer?.dispose()
        this.renderer?.domElement.remove()
    }
}
