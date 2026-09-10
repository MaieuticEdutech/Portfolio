<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('keeps the client list behind auth', function () {
    auth()->logout();

    $this->get(route('admin.clients'))->assertRedirect(route('admin.login'));
});

it('lists clients including unpublished ones', function () {
    PortfolioClient::factory()->create(['name' => 'Live Client']);
    PortfolioClient::factory()->unpublished()->create(['name' => 'Hidden Client']);

    $this->get(route('admin.clients'))
        ->assertOk()
        ->assertSee('Live Client')
        ->assertSee('Hidden Client');
});

it('searches case-insensitively', function () {
    PortfolioClient::factory()->create(['name' => 'Narayana Health']);
    PortfolioClient::factory()->create(['name' => 'Jade Global']);

    Livewire::test('admin.clients')
        ->set('search', 'naraya')
        ->assertSee('Narayana Health')
        ->assertDontSee('Jade Global');
});

it('toggles a client between live and hidden', function () {
    $client = PortfolioClient::factory()->create(['is_published' => true]);

    Livewire::test('admin.clients')->call('togglePublished', $client->id);
    expect($client->fresh()->is_published)->toBeFalse();

    Livewire::test('admin.clients')->call('togglePublished', $client->id);
    expect($client->fresh()->is_published)->toBeTrue();
});

it('counts how many films are ready', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();
    PortfolioVideo::factory()->for($client, 'client')->create();

    $row = Livewire::test('admin.clients')->instance()->clients->sole();

    expect($row->videos_count)->toBe(2)
        ->and($row->ready_videos_count)->toBe(1);
});

it('filters to clients still missing assets', function () {
    $done = PortfolioClient::factory()->create(['name' => 'Finished Co', 'logo_path' => 'logos/done.svg']);
    PortfolioVideo::factory()->for($done, 'client')->uploaded()->create();

    PortfolioClient::factory()->create(['name' => 'Needs Work', 'logo_path' => null]);

    Livewire::test('admin.clients')
        ->set('onlyIncomplete', true)
        ->assertSee('Needs Work')
        ->assertDontSee('Finished Co');
});
