/**
 * Draws a portfolio card to a 2D canvas so it can be used as a WebGL texture.
 *
 * This is what makes the *contents* bend rather than a frame around them: once
 * the gradient, logo and caption are pixels in one texture, the vertex shader
 * deforms all of it together, so the logo and the type curve with the sheet.
 */

const roundedPath = (ctx, x, y, w, h, radii) => {
    const [tl, tr, br, bl] = radii
    ctx.beginPath()
    ctx.moveTo(x + tl, y)
    ctx.lineTo(x + w - tr, y)
    ctx.quadraticCurveTo(x + w, y, x + w, y + tr)
    ctx.lineTo(x + w, y + h - br)
    ctx.quadraticCurveTo(x + w, y + h, x + w - br, y + h)
    ctx.lineTo(x + bl, y + h)
    ctx.quadraticCurveTo(x, y + h, x, y + h - bl)
    ctx.lineTo(x, y + tl)
    ctx.quadraticCurveTo(x, y, x + tl, y)
    ctx.closePath()
}

export async function drawCardArtwork({
    width = 900,
    height = 1125,
    highlight = '#15D9A1',
    tone = '#00615C',
    radii = [128, 32, 128, 32],
    index = '01',
    sector = 'Education',
    title = '',
    meta = '',
    accent = '#15D9A1',
    logoUrl = null,
} = {}) {
    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')

    // The art-directed silhouette is baked into the texture's alpha, so the
    // asymmetric corners survive the bend instead of being a CSS frame.
    roundedPath(ctx, 0, 0, width, height, radii)
    ctx.clip()

    const base = ctx.createLinearGradient(0, 0, width * 0.8, height)
    base.addColorStop(0, tone)
    base.addColorStop(0.78, '#0C1817')
    ctx.fillStyle = base
    ctx.fillRect(0, 0, width, height)

    const glow = ctx.createRadialGradient(0, 0, 0, 0, 0, width * 0.95)
    glow.addColorStop(0, highlight)
    glow.addColorStop(0.62, 'rgba(0,0,0,0)')
    ctx.fillStyle = glow
    ctx.fillRect(0, 0, width, height)

    if (logoUrl) {
        try {
            const logo = await loadImage(logoUrl)
            const plate = { x: width * 0.12, y: height * 0.13, w: width * 0.76, h: height * 0.2 }
            ctx.save()
            roundedPath(ctx, plate.x, plate.y, plate.w, plate.h, [28, 28, 28, 28])
            ctx.fillStyle = '#ffffff'
            ctx.shadowColor = 'rgba(0,0,0,0.35)'
            ctx.shadowBlur = 40
            ctx.shadowOffsetY = 14
            ctx.fill()
            ctx.shadowColor = 'transparent'
            ctx.clip()
            const pad = plate.h * 0.2
            const box = { w: plate.w - pad * 2, h: plate.h - pad * 2 }
            const scale = Math.min(box.w / logo.width, box.h / logo.height)
            const dw = logo.width * scale
            const dh = logo.height * scale
            ctx.drawImage(logo, plate.x + (plate.w - dw) / 2, plate.y + (plate.h - dh) / 2, dw, dh)
            ctx.restore()
        } catch {
            // A missing logo is not worth failing the card over.
        }
    }

    const scrim = ctx.createLinearGradient(0, height * 0.45, 0, height)
    scrim.addColorStop(0, 'rgba(0,0,0,0)')
    scrim.addColorStop(1, 'rgba(0,0,0,0.82)')
    ctx.fillStyle = scrim
    ctx.fillRect(0, height * 0.45, width, height * 0.55)

    const left = width * 0.09
    let y = height * 0.8

    ctx.fillStyle = accent
    ctx.font = `600 ${Math.round(width * 0.028)}px "Instrument Sans", system-ui, sans-serif`
    ctx.letterSpacing = `${Math.round(width * 0.008)}px`
    ctx.fillText(index, left, y)

    const indexW = ctx.measureText(index).width
    ctx.strokeStyle = accent
    ctx.globalAlpha = 0.45
    ctx.lineWidth = 2
    ctx.beginPath()
    ctx.moveTo(left + indexW + 22, y - 8)
    ctx.lineTo(left + indexW + 74, y - 8)
    ctx.stroke()
    ctx.globalAlpha = 1
    ctx.fillText(sector.toUpperCase(), left + indexW + 92, y)
    ctx.letterSpacing = '0px'

    y += height * 0.055
    ctx.fillStyle = '#ffffff'
    ctx.font = `700 ${Math.round(width * 0.072)}px "Instrument Sans", system-ui, sans-serif`
    for (const line of wrap(ctx, title, width * 0.82).slice(0, 2)) {
        ctx.fillText(line, left, y)
        y += width * 0.082
    }

    if (meta) {
        ctx.fillStyle = 'rgba(255,255,255,0.62)'
        ctx.font = `400 ${Math.round(width * 0.032)}px "Instrument Sans", system-ui, sans-serif`
        ctx.fillText(meta, left, y)
    }

    return canvas
}

function wrap(ctx, text, maxWidth) {
    const words = String(text).split(' ')
    const lines = []
    let line = ''
    for (const word of words) {
        const next = line ? `${line} ${word}` : word
        if (ctx.measureText(next).width > maxWidth && line) {
            lines.push(line)
            line = word
        } else {
            line = next
        }
    }
    if (line) lines.push(line)
    return lines
}

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image()
        img.crossOrigin = 'anonymous'
        img.onload = () => resolve(img)
        img.onerror = reject
        img.src = src
    })
}
