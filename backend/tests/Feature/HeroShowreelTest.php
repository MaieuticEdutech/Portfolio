<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;

it('plays the first uploaded film as the showreel', function () {
    $client = PortfolioClient::factory()->create(['name' => 'Strides Ltd']);
    PortfolioVideo::factory()->for($client, 'client')->uploaded()->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('Now showing')
        ->assertSee('Strides Ltd')
        ->assertSee('autoplay', false)
        ->assertSee('playsinline', false);
});

it('shows no panel at all when nothing is uploaded', function () {
    PortfolioClient::factory()->create();

    // A placeholder here would advertise having nothing to show, so the
    // headline simply takes the full measure instead.
    $this->get('/')
        ->assertOk()
        ->assertDontSee('Now showing')
        ->assertDontSee('Showreel')
        ->assertDontSee('<video', false);
});

it('will not pull a showreel from an unpublished client', function () {
    $hidden = PortfolioClient::factory()->unpublished()->create(['name' => 'Hidden Corp']);
    PortfolioVideo::factory()->for($hidden, 'client')->uploaded()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Now showing')
        ->assertDontSee('Hidden Corp');
});

it('ignores films that are only pasted links', function () {
    $client = PortfolioClient::factory()->create();
    PortfolioVideo::factory()->for($client, 'client')->youtube()->create();

    // A <video> tag cannot play a YouTube link, so the panel stays generated.
    $this->get('/')->assertOk()->assertDontSee('Now showing');
});

it('keeps the drifting aurora behind the reduced-motion opt-out', function () {
    PortfolioClient::factory()->create();

    $this->get('/')->assertSee('hero-drift-one', false);

    // The stylesheet carries the opt-out; assert it still names what renders.
    expect(file_get_contents(resource_path('css/app.css')))
        ->toContain('prefers-reduced-motion')
        ->toContain('.hero-drift-one');
});

it('gives the headline the full measure with no film to show', function () {
    PortfolioClient::factory()->create();

    $this->get('/')->assertSee('lg:text-8xl', false);
});
