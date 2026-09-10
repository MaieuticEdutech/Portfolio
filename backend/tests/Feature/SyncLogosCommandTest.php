<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('links a logo file to the client with the same slug', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Emeritus']);
    Storage::disk('public')->put('logos/emeritus.webp', 'webp-bytes');

    $this->artisan('portfolio:sync-logos')->assertSuccessful();

    expect($client->fresh()->logo_path)->toBe('logos/emeritus.webp')
        ->and($client->fresh()->logoUrl())->toEndWith('/storage/logos/emeritus.webp');
});

it('clears a logo path whose file has been removed', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Wiley', 'logo_path' => 'logos/wiley.webp']);

    $this->artisan('portfolio:sync-logos')->assertSuccessful();

    expect($client->fresh()->logo_path)->toBeNull();
});

it('reports files that match no client and clients that have no file', function () {
    PortfolioClient::factory()->create(['name' => 'Symbiosis']);
    Storage::disk('public')->put('logos/sandip.webp', 'webp-bytes');

    $this->artisan('portfolio:sync-logos')
        ->expectsOutputToContain('no matching client slug: sandip')
        ->expectsOutputToContain('without a logo: symbiosis')
        ->assertSuccessful();
});

it('ignores files that are not images', function () {
    PortfolioClient::factory()->create(['name' => 'Wiley']);
    Storage::disk('public')->put('logos/wiley.txt', 'not a logo');

    $this->artisan('portfolio:sync-logos')->assertSuccessful();

    expect(PortfolioClient::where('slug', 'wiley')->value('logo_path'))->toBeNull();
});

it('renders the logo image in the grid tile once linked', function () {
    PortfolioClient::factory()->create(['name' => 'Emeritus', 'logo_path' => 'logos/emeritus.webp']);

    $this->get('/')
        ->assertOk()
        ->assertSee('/storage/logos/emeritus.webp')
        ->assertSee('alt="Emeritus logo"', false);
});
