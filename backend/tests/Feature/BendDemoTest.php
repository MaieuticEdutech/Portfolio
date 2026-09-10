<?php

use App\Models\PortfolioClient;

it('serves the isolated bend demo', function () {
    PortfolioClient::factory()->create(['name' => 'REVA University Online', 'logo_path' => 'logos/reva.webp']);

    $this->get('/lab/bend')
        ->assertOk()
        ->assertSee('data-bend-demo', false)
        ->assertSee('data-bend-stage', false);
});

it('offers every hover state the brief asks to verify', function () {
    PortfolioClient::factory()->create(['logo_path' => 'logos/x.webp']);

    $page = $this->get('/lab/bend');

    foreach (['top-left', 'top-right', 'centre', 'bottom-left', 'bottom-right', 'leave'] as $state) {
        $page->assertSee('data-bend-state="'.$state.'"', false);
    }
});

it('exposes the shader values for tuning', function () {
    PortfolioClient::factory()->create(['logo_path' => 'logos/x.webp']);

    $page = $this->get('/lab/bend');

    foreach (['bendDepth', 'bendRadius', 'bendFalloff', 'uvPull'] as $key) {
        $page->assertSee('data-bend-tune="'.$key.'"', false);
    }
});

it('feeds the card real client data', function () {
    PortfolioClient::factory()->create([
        'name' => 'Strides Ltd',
        'project_type' => 'Brand anthem film',
        'year' => '2024',
        'logo_path' => 'logos/strides.webp',
    ]);

    $this->get('/lab/bend')
        ->assertSee('Strides Ltd')
        ->assertSee('Brand anthem film')
        ->assertSee('logos/strides.webp', false);
});

it('bends geometry in a vertex shader, not the fragment shader alone', function () {
    $shader = file_get_contents(resource_path('js/bend-card.js'));

    // UV distortion alone warps the picture but leaves the rectangle intact.
    // Displacing position in the vertex shader is what changes the silhouette.
    expect($shader)->toContain('vec3 pos = position;')
        ->and($shader)->toContain('pos.z += influence * uDepth;')
        ->and($shader)->toContain('smoothstep');
});

it('eases the cursor rather than tracking it raw', function () {
    $source = file_get_contents(resource_path('js/bend-card.js'));

    expect($source)->toContain('this.mouse.lerp(this.target, this.cfg.smoothing)')
        ->and($source)->toContain('(this.hoverTarget - this.hover) * this.cfg.hoverEase');
});

it('ignores touch pointers, since a finger has no hover', function () {
    expect(file_get_contents(resource_path('js/bend-card.js')))
        ->toContain("if (e.pointerType === 'touch') return");
});

it('tears down webgl resources on destroy', function () {
    $source = file_get_contents(resource_path('js/bend-card.js'));

    foreach (['geometry.dispose()', 'material?.dispose()', 'texture?.dispose()', 'renderer?.dispose()', 'cancelAnimationFrame'] as $call) {
        expect($source)->toContain($call);
    }
});

it('keeps three.js out of the main bundle', function () {
    $entry = file_get_contents(resource_path('js/app.js'));

    // A static import would ship the whole renderer to every visitor.
    expect($entry)->toContain("await import('./bend-demo')")
        ->and($entry)->not->toContain("from './bend-demo'");
});
