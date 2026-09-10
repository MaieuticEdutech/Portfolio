<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Livewire\Livewire;

it('renders the portfolio page with published clients', function () {
    PortfolioClient::factory()->educational()->create(['name' => 'Visible University']);
    PortfolioClient::factory()->unpublished()->create(['name' => 'Hidden Corp']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Visible University')
        ->assertDontSee('Hidden Corp');
});

it('counts only published clients per category', function () {
    PortfolioClient::factory()->count(3)->educational()->create();
    PortfolioClient::factory()->count(2)->corporate()->create();
    PortfolioClient::factory()->educational()->unpublished()->create();

    $counts = Livewire::test('portfolio-grid')->instance()->counts;

    expect($counts)->toBe(['all' => 5, 'educational' => 3, 'corporate' => 2]);
});

it('filters tiles by category', function () {
    PortfolioClient::factory()->educational()->create(['name' => 'Teal Academy']);
    PortfolioClient::factory()->corporate()->create(['name' => 'Crimson Industries']);

    Livewire::test('portfolio-grid')
        ->assertSee('Teal Academy')
        ->assertSee('Crimson Industries')
        ->call('filterBy', 'corporate')
        ->assertSee('Crimson Industries')
        ->assertDontSee('Teal Academy');
});

it('ignores an unknown category and falls back to all', function () {
    PortfolioClient::factory()->educational()->create(['name' => 'Teal Academy']);

    Livewire::test('portfolio-grid')
        ->call('filterBy', 'nonsense')
        ->assertSet('category', 'all')
        ->assertSee('Teal Academy');
});

it('searches clients by name', function () {
    PortfolioClient::factory()->create(['name' => 'Narayana Health']);
    PortfolioClient::factory()->create(['name' => 'Jade Global']);

    Livewire::test('portfolio-grid')
        ->set('search', 'naraya')
        ->assertSee('Narayana Health')
        ->assertDontSee('Jade Global');
});

it('shows an empty state when nothing matches', function () {
    PortfolioClient::factory()->create(['name' => 'Jade Global']);

    Livewire::test('portfolio-grid')
        ->set('search', 'zzzz')
        ->assertSee('No clients match');
});

it('opens a client detail with its videos in sort order', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
    PortfolioVideo::factory()->for($client, 'client')->create(['title' => 'Second Film', 'sort_order' => 2]);
    PortfolioVideo::factory()->for($client, 'client')->create(['title' => 'First Film', 'sort_order' => 1]);

    Livewire::test('portfolio-grid')
        ->call('select', $client->id)
        ->assertSeeInOrder(['First Film', 'Second Film'])
        ->assertSee('Footage coming soon');
});

it('will not open an unpublished client', function () {
    $client = PortfolioClient::factory()->unpublished()->create(['name' => 'Hidden Corp']);

    Livewire::test('portfolio-grid')
        ->call('select', $client->id)
        ->assertDontSee('Hidden Corp');
});

it('closes the client detail', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);

    Livewire::test('portfolio-grid')
        ->call('select', $client->id)
        ->assertSet('selectedClientId', $client->id)
        ->call('close')
        ->assertSet('selectedClientId', null);
});

it('drops the open detail when the filter changes', function () {
    $client = PortfolioClient::factory()->educational()->create();

    Livewire::test('portfolio-grid')
        ->call('select', $client->id)
        ->call('filterBy', 'corporate')
        ->assertSet('selectedClientId', null);
});

it('orders big tiles ahead of smaller ones at the same sort order', function () {
    PortfolioClient::factory()->create(['name' => 'Small One', 'tile_size' => 'small', 'sort_order' => 0]);
    PortfolioClient::factory()->create(['name' => 'Big One', 'tile_size' => 'big', 'sort_order' => 0]);
    PortfolioClient::factory()->create(['name' => 'Med One', 'tile_size' => 'med', 'sort_order' => 0]);

    Livewire::test('portfolio-grid')
        ->assertSeeInOrder(['Big One', 'Med One', 'Small One']);
});

it('floats published clients across the header wall', function () {
    PortfolioClient::factory()->corporate()->create(['name' => 'Floating Corp']);
    PortfolioClient::factory()->unpublished()->create(['name' => 'Sunken Corp']);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['data-logo-marquee', 'Floating Corp'], false)
        ->assertDontSee('Sunken Corp');
});
