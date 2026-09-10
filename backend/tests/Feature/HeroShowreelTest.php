<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;

it('plays the first uploaded film as the showreel', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('Now showing')
        ->assertSee('Strides Ltd')
        ->assertSee('autoplay', false)
        ->assertSee('playsinline', false);
});

it('falls back to the generated panel when nothing is uploaded', function () {
    PortfolioClient::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('Showreel')
        ->assertSee('Upload a film in the studio')
        ->assertDontSee('Now showing');
});

it('will not pull a showreel from an unpublished client', function () {
    $hidden = PortfolioClient::factory()->unpublished()->create(['name' => 'Hidden Corp']);
    PortfolioVideo::factory()->for($hidden, 'client')->uploaded()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Now showing')
        ->assertDontSee('Hidden Corp');
});

it('ignores films that are only pasted links', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->youtube()->create();

    // A <video> tag cannot play a YouTube link, so the panel stays generated.
    $this->get('/')->assertOk()->assertDontSee('Now showing');
});

it('keeps hero motion out of the way of reduced-motion users', function () {
    PortfolioClient::factory()->create();

    // The stylesheet, not the markup, carries the opt-out; assert the hooks the
    // reduced-motion block targets are the ones actually rendered.
    $this->get('/')
        ->assertSee('hero-drift-one', false)
        ->assertSee('hero-sprockets', false)
        // The loop is hidden for reduced motion, uncovering the still gradient.
        ->assertSee('motion-reduce:hidden', false);
});

it('backs the placeholder with the generated loop', function () {
    PortfolioClient::factory()->create();

    $this->get('/')->assertSee('media/showreel-loop.webp', false);

    expect(public_path('media/showreel-loop.webp'))->toBeReadableFile();
});

it('ships the loop as a genuinely animated file', function () {
    $bytes = file_get_contents(public_path('media/showreel-loop.webp'));

    // GD cannot decode animated WebP, so read the container: an animation
    // carries an ANIM chunk and one ANMF chunk per frame. A still file has
    // neither, which is the regression worth catching if the asset is ever
    // re-exported by hand.
    expect(substr($bytes, 0, 4))->toBe('RIFF')
        ->and(substr($bytes, 8, 4))->toBe('WEBP')
        ->and(str_contains($bytes, 'ANIM'))->toBeTrue()
        ->and(substr_count($bytes, 'ANMF'))->toBeGreaterThan(30);
});
