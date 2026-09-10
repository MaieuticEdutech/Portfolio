<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Livewire\Livewire;

it('previews the first film that has footage uploaded', function () {
    $client = PortfolioClient::factory()->create();

    PortfolioVideo::factory()->for($client, 'client')->create(['sort_order' => 0]);
    PortfolioVideo::factory()->for($client, 'client')->youtube()->create(['sort_order' => 1]);
    $uploaded = PortfolioVideo::factory()->for($client, 'client')->uploaded()->create(['sort_order' => 2]);

    expect($client->previewVideo?->id)->toBe($uploaded->id);
});

it('respects film order when several are uploaded', function () {
    $client = PortfolioClient::factory()->create();

    $second = PortfolioVideo::factory()->for($client, 'client')->uploaded()->create(['sort_order' => 5]);
    $first = PortfolioVideo::factory()->for($client, 'client')->uploaded()->create(['sort_order' => 1]);

    expect($client->previewVideo?->id)->toBe($first->id)
        ->and($client->previewVideo?->id)->not->toBe($second->id);
});

it('has no preview when a client only has pasted links', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->youtube()->create();

    expect($client->previewVideo)->toBeNull();
});

it('renders a muted looping video on tiles that have footage', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();

    $html = Livewire::test('portfolio-grid')->html();

    expect($html)->toContain('x-ref="preview"')
        ->and($html)->toContain('muted')
        ->and($html)->toContain('loop')
        ->and($html)->toContain('playsinline')
        ->and($html)->toContain('preload="none"');
});

it('leaves tiles untouched when no footage exists yet', function () {
    PortfolioClient::factory()->create(['name' => 'Strides Ltd']);

    $html = Livewire::test('portfolio-grid')->html();

    expect($html)->toContain('Strides Ltd')
        ->and($html)->not->toContain('x-ref="preview"');
});

it('keeps the preview out of the accessibility tree and tab order', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();

    $html = Livewire::test('portfolio-grid')->html();

    expect($html)->toContain('aria-hidden="true"')
        ->and($html)->toContain('tabindex="-1"');
});

it('no longer sweeps a sheen across every tile', function () {
    PortfolioClient::factory()->count(3)->create();

    expect(Livewire::test('portfolio-grid')->html())->not->toContain('skew-x-12');
});

it('loads the preview without an extra query per tile', function () {
    PortfolioClient::factory()->count(10)->create()->each(
        fn ($client) => PortfolioVideo::factory()->for($client, 'client')->uploaded()->create()
    );

    DB::enableQueryLog();
    Livewire::test('portfolio-grid')->html();
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Eager loaded, so tile count must not drive query count.
    expect($queries)->toBeLessThan(10);
});
