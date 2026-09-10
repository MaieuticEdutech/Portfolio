<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Facades\Blade;

it('renders each crew variant with its own rigging', function (string $variant, string $movingPart) {
    $svg = Blade::render('<x-studio-figure variant="'.$variant.'" />');

    expect($svg)->toContain('figure-'.$variant)
        ->and($svg)->toContain($movingPart)
        ->and($svg)->toContain('figure-bob');
})->with([
    ['camera', 'figure-prop-camera'],
    ['clapper', 'figure-clapper-arm'],
    ['boom', 'figure-prop-boom'],
]);

it('keeps the figures out of the accessibility tree', function () {
    expect(Blade::render('<x-studio-figure variant="camera" />'))->toContain('aria-hidden="true"');
});

it('can be mirrored so a pair does not face the same way', function () {
    expect(Blade::render('<x-studio-figure variant="clapper" :flip="true" />'))->toContain('-scale-x-100')
        ->and(Blade::render('<x-studio-figure variant="clapper" />'))->not->toContain('-scale-x-100');
});

it('places the crew on the portfolio page without touching the grid', function () {
    PortfolioClient::factory()->create(['name' => 'Strides Ltd']);

    $this->get('/')
        ->assertOk()
        ->assertSee('figure-camera', false)
        ->assertSee('figure-clapper', false)
        ->assertSee('figure-boom', false)
        // Decorative only: never inside a tile, and never clickable.
        ->assertSee('pointer-events-none', false)
        ->assertSee('Strides Ltd');
});
