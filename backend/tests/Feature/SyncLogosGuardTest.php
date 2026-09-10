<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Facades\Storage;

/**
 * The command clears the logo path of any client without a matching file.
 * Pointed at the wrong disk that wipes every client, so an empty directory
 * has to fail loudly instead.
 */
it('refuses to clear logo paths when a remote disk has no logo files', function () {
    config(['filesystems.logos' => 's3']);
    Storage::fake('s3');

    $client = PortfolioClient::factory()->create(['logo_path' => 'logos/acme.webp']);

    $this->artisan('portfolio:sync-logos')
        ->expectsOutputToContain('Refusing to clear existing logo paths')
        ->assertFailed();

    expect($client->fresh()->logo_path)->toBe('logos/acme.webp');
});

it('still clears when the operator insists with --force', function () {
    config(['filesystems.logos' => 's3']);
    Storage::fake('s3');

    $client = PortfolioClient::factory()->create(['logo_path' => 'logos/gone.webp']);

    $this->artisan('portfolio:sync-logos', ['--force' => true])->assertSuccessful();

    expect($client->fresh()->logo_path)->toBeNull();
});

it('reads from whichever disk LOGO_DISK names', function () {
    config(['filesystems.logos' => 's3']);
    Storage::fake('s3');
    Storage::fake('public');

    Storage::disk('s3')->put('logos/acme-media.webp', 'binary');
    $client = PortfolioClient::factory()->create(['name' => 'Acme Media']);

    $this->artisan('portfolio:sync-logos')->assertSuccessful();

    expect($client->fresh()->logo_path)->toBe('logos/acme-media.webp');
});
