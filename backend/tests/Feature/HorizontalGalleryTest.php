<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;

it('renders the scroll-driven reel with its hooks', function () {
    PortfolioClient::factory()->count(4)->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('data-hgallery', false)
        ->assertSee('data-hgallery-viewport', false)
        ->assertSee('data-hgallery-track', false)
        ->assertSee('data-hgallery-card', false);
});

it('clips the track inside a pinned viewport', function () {
    PortfolioClient::factory()->count(4)->create();

    $html = $this->get('/')->getContent();

    // The window is fixed height and hides overflow; the track is wider than it.
    expect($html)->toContain('h-screen')
        ->and($html)->toContain('overflow-hidden')
        ->and($html)->toContain('w-max');
});

it('curates the reel rather than showing every client', function () {
    PortfolioClient::factory()->count(20)->create();

    $html = $this->get('/')->getContent();

    expect(substr_count($html, 'data-hgallery-card'))->toBe(8);
});

it('varies card silhouettes so neighbours never match', function () {
    PortfolioClient::factory()->count(8)->create();

    $html = $this->get('/')->getContent();
    preg_match_all('/rounded-\[[^\]]+_[^\]]+\]/', $html, $m);

    expect(array_unique($m[0]))->toHaveCount(4);
});

it('numbers and labels each card editorially', function () {
    PortfolioClient::factory()->educational()->create(['name' => 'Teal Academy']);

    $this->get('/')
        ->assertSee('01')
        ->assertSee('Education')
        ->assertSee('Teal Academy');
});

it('carries a film into the reel when one is uploaded', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();

    $this->get('/')->assertSee('playsinline', false);
});

it('lazily loads card logos', function () {
    PortfolioClient::factory()->create(['logo_path' => 'logos/acme.webp']);

    $this->get('/')
        ->assertSee('loading="lazy"', false)
        ->assertSee('decoding="async"', false);
});
