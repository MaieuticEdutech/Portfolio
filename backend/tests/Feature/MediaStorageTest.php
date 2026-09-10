<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;

/**
 * These prove the app is genuinely disk-agnostic: the same code paths must
 * work when the disks point at object storage instead of local disk.
 * They run against a faked s3 disk, so no real R2 credentials are needed.
 */
beforeEach(function () {
    config(['filesystems.films' => 's3', 'filesystems.logos' => 's3']);
    Storage::fake('s3');

    $this->actingAs(User::factory()->create());
    $this->client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
});

/**
 * Storage::fake swaps in a local disk, whose url() is /storage/... . These
 * URL assertions need the real S3 driver, so rebuild it from config. No
 * network happens - url() is pure string building.
 */
function useRealS3Disk(): void
{
    Storage::forgetDisk('s3');

    config(['filesystems.disks.s3' => [
        'driver' => 's3',
        'key' => 'key',
        'secret' => 'secret',
        'region' => 'auto',
        'bucket' => 'portfolio',
        'endpoint' => 'https://account.r2.cloudflarestorage.com',
        'use_path_style_endpoint' => true,
        'url' => 'https://media.example.com',
        'request_checksum_calculation' => 'when_required',
        'response_checksum_validation' => 'when_required',
    ]]);
}

it('stores an uploaded film on the s3 disk when FILM_DISK is s3', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Brand Anthem')
        ->set('videoFile', UploadedFile::fake()->create('anthem.mp4', 1024, 'video/mp4'))
        ->call('save')
        ->assertHasNoErrors();

    $video = $this->client->videos()->sole();

    Storage::disk('s3')->assertExists($video->video_path);
    Storage::disk('public')->assertMissing($video->video_path);
});

it('deletes from s3 when footage is replaced', function () {
    $video = PortfolioVideo::factory()->for($this->client, 'client')->create();

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('first.mp4', 256, 'video/mp4'))
        ->call('save');

    $first = $video->fresh()->video_path;

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('second.mp4', 256, 'video/mp4'))
        ->call('save');

    Storage::disk('s3')->assertMissing($first);
    Storage::disk('s3')->assertExists($video->fresh()->video_path);
});

it('builds public urls from AWS_URL rather than a signed link', function () {
    useRealS3Disk();

    $video = PortfolioVideo::factory()->for($this->client, 'client')->create([
        'video_path' => 'portfolio/films/strides-ltd/film.mp4',
        'thumbnail_path' => 'portfolio/thumbnails/strides-ltd/poster.jpg',
    ]);

    expect($video->source())->toBe('https://media.example.com/portfolio/films/strides-ltd/film.mp4')
        ->and($video->thumbnailUrl())->toBe('https://media.example.com/portfolio/thumbnails/strides-ltd/poster.jpg')
        ->and($video->source())->not->toContain('X-Amz-Signature');
});

it('resolves client logo urls through the media disk', function () {
    useRealS3Disk();

    $client = PortfolioClient::factory()->create(['logo_path' => 'portfolio/logos/acme.svg']);

    expect($client->logoUrl())->toBe('https://media.example.com/portfolio/logos/acme.svg');
});

it('turns on direct-to-bucket uploads when the temporary disk is s3', function () {
    config(['livewire.temporary_file_upload.disk' => 's3']);

    expect(FileUploadConfiguration::isUsingS3())->toBeTrue();
});

it('keeps uploads flowing through PHP when no temporary disk is set', function () {
    config(['livewire.temporary_file_upload.disk' => null]);

    expect(FileUploadConfiguration::isUsingS3())->toBeFalse();
});

it('leaves visibility unset, because R2 has no ACLs', function () {
    expect(config('filesystems.disks.s3.visibility'))->toBeNull();
});

it('only sends checksums when required, because R2 rejects the defaults', function () {
    expect(config('filesystems.disks.s3.request_checksum_calculation'))->toBe('when_required')
        ->and(config('filesystems.disks.s3.response_checksum_validation'))->toBe('when_required');
});

it('accepts films far larger than the livewire default cap', function () {
    // The 12MB default would reject this outright.
    expect((int) str_replace('max:', '', collect(config('livewire.temporary_file_upload.rules'))->last()))
        ->toBeGreaterThan(1_000_000);
});
