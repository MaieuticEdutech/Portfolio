<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;

it('generates a slug from the name', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Terumo India Pvt Ltd']);

    expect($client->slug)->toBe('terumo-india-pvt-ltd');
});

it('de-duplicates slugs across clients with the same name', function () {
    PortfolioClient::factory()->create(['name' => 'Acme Media']);
    $second = PortfolioClient::factory()->create(['name' => 'Acme Media']);

    expect($second->slug)->toBe('acme-media-2');
});

it('keeps its own slug when re-saved', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Acme Media']);

    $client->update(['year' => '2025']);

    expect($client->fresh()->slug)->toBe('acme-media');
});

it('drops empty gradient stops', function () {
    $client = PortfolioClient::factory()->create([
        'accent_gradient_start' => '#69FFF7',
        'accent_gradient_mid' => null,
        'accent_gradient_end' => '#00615C',
    ]);

    expect($client->gradientStops())->toBe(['#69FFF7', '#00615C']);
});

it('prefers an uploaded file over a pasted url', function () {
    $video = PortfolioVideo::factory()->youtube()->uploaded()->create();

    expect($video->isUploaded())->toBeTrue()
        ->and($video->source())->toContain('portfolio/videos/sample.mp4')
        ->and($video->embedUrl())->toBeNull();
});

it('is pending when it has no source at all', function () {
    expect(PortfolioVideo::factory()->create()->isPending())->toBeTrue();
});

it('builds embed urls for known providers', function (string $url, ?string $expected) {
    $video = PortfolioVideo::factory()->create(['video_url' => $url]);

    expect($video->embedUrl())->toBe($expected);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube.com/embed/dQw4w9WgXcQ'],
    ['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube.com/embed/dQw4w9WgXcQ'],
    ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
    ['https://vimeo.com/video/76979871', 'https://player.vimeo.com/video/76979871'],
    ['https://example.com/some/film.mov', null],
]);

it('deletes videos along with the client', function () {
    $client = PortfolioClient::factory()->has(PortfolioVideo::factory()->count(3), 'videos')->create();

    $client->delete();

    expect(PortfolioVideo::count())->toBe(0);
});
