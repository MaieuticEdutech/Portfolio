<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake(config('filesystems.media'));
    $this->actingAs(User::factory()->create());
    $this->client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
});

it('keeps the film manager behind auth', function () {
    auth()->logout();

    $this->get(route('admin.clients.films', $this->client))
        ->assertRedirect(route('admin.login'));
});

it('uploads a film and stores it on the media disk', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Brand Anthem')
        ->set('videoFile', UploadedFile::fake()->create('anthem.mp4', 2048, 'video/mp4'))
        ->call('save')
        ->assertHasNoErrors();

    $video = $this->client->videos()->sole();

    expect($video->title)->toBe('Brand Anthem')
        ->and($video->video_path)->toContain('portfolio/films/strides-ltd')
        ->and($video->isUploaded())->toBeTrue();

    Storage::disk(config('filesystems.media'))->assertExists($video->video_path);
});

it('accepts a pasted link instead of a file', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Culture Film')
        ->set('videoUrl', 'https://vimeo.com/76979871')
        ->call('save')
        ->assertHasNoErrors();

    $video = $this->client->videos()->sole();

    expect($video->video_path)->toBeNull()
        ->and($video->embedUrl())->toBe('https://player.vimeo.com/video/76979871');
});

it('rejects a file that is not a video', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Not a film')
        ->set('videoFile', UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasErrors('videoFile');

    expect($this->client->videos()->count())->toBe(0);
});

it('rejects a malformed link', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Bad link')
        ->set('videoUrl', 'not-a-url')
        ->call('save')
        ->assertHasErrors('videoUrl');
});

it('requires a title', function () {
    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

it('deletes the old file when footage is replaced', function () {
    $video = PortfolioVideo::factory()->for($this->client, 'client')->create();

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('first.mp4', 512, 'video/mp4'))
        ->call('save');

    $first = $video->fresh()->video_path;

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('second.mp4', 512, 'video/mp4'))
        ->call('save');

    $second = $video->fresh()->video_path;

    expect($second)->not->toBe($first);
    Storage::disk(config('filesystems.media'))->assertMissing($first);
    Storage::disk(config('filesystems.media'))->assertExists($second);
});

it('clears footage but keeps the slot', function () {
    $video = PortfolioVideo::factory()->for($this->client, 'client')->create();

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('film.mp4', 512, 'video/mp4'))
        ->call('save');

    $path = $video->fresh()->video_path;

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('removeFootage', $video->id);

    expect($video->fresh())->not->toBeNull()
        ->and($video->fresh()->isPending())->toBeTrue();

    Storage::disk(config('filesystems.media'))->assertMissing($path);
});

it('deletes a film and its file', function () {
    $video = PortfolioVideo::factory()->for($this->client, 'client')->create();

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('edit', $video->id)
        ->set('videoFile', UploadedFile::fake()->create('film.mp4', 512, 'video/mp4'))
        ->call('save');

    $path = $video->fresh()->video_path;

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('deleteFilm', $video->id);

    expect(PortfolioVideo::find($video->id))->toBeNull();
    Storage::disk(config('filesystems.media'))->assertMissing($path);
});

it('reorders films', function () {
    $first = PortfolioVideo::factory()->for($this->client, 'client')->create(['title' => 'First', 'sort_order' => 0]);
    $second = PortfolioVideo::factory()->for($this->client, 'client')->create(['title' => 'Second', 'sort_order' => 1]);

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('moveUp', $second->id);

    expect($this->client->videos()->pluck('title')->all())->toBe(['Second', 'First'])
        ->and($first->fresh()->sort_order)->toBe(1);
});

it('will not touch a film belonging to another client', function () {
    $other = PortfolioClient::factory()->create();
    $foreign = PortfolioVideo::factory()->for($other, 'client')->create();

    expect(fn () => Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('deleteFilm', $foreign->id))
        ->toThrow(ModelNotFoundException::class);

    expect(PortfolioVideo::find($foreign->id))->not->toBeNull();
});

it('appends a new film to the end of the order', function () {
    PortfolioVideo::factory()->for($this->client, 'client')->create(['sort_order' => 5]);

    Livewire::test('admin.client-films', ['client' => $this->client])
        ->call('newFilm')
        ->set('title', 'Latest')
        ->call('save');

    expect(PortfolioVideo::where('title', 'Latest')->sole()->sort_order)->toBe(6);
});
