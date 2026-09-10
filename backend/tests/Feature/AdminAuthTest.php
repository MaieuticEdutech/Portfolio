<?php

use App\Models\User;
use Livewire\Livewire;

it('redirects guests from the dashboard to the login page', function () {
    $this->get('/studio')->assertRedirect(route('admin.login'));
});

it('shows the login page to guests', function () {
    $this->get('/studio/login')
        ->assertOk()
        ->assertSee('Sign in to the studio')
        ->assertSee('no public sign-up');
});

it('keeps the login page out of search results', function () {
    $this->get('/studio/login')->assertSee('noindex', escape: false);
});

it('lets a studio user sign in', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    Livewire::test('admin.login')
        ->set('email', $user->email)
        ->set('password', 'correct-horse')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    Livewire::test('admin.login')
        ->set('email', $user->email)
        ->set('password', 'wrong')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

it('requires an email and password', function () {
    Livewire::test('admin.login')
        ->call('login')
        ->assertHasErrors(['email' => 'required', 'password' => 'required']);
});

it('throttles after five failed attempts', function () {
    $user = User::factory()->create(['password' => 'correct-horse']);

    $component = Livewire::test('admin.login')
        ->set('email', $user->email)
        ->set('password', 'wrong');

    foreach (range(1, 5) as $ignored) {
        $component->call('login');
    }

    $component->call('login');

    expect($component->errors()->first('email'))->toContain('Too many login attempts');
});

it('shows the dashboard to a signed-in user', function () {
    $this->actingAs(User::factory()->create(['name' => 'Srusti']))
        ->get('/studio')
        ->assertOk()
        ->assertSee('Studio overview')
        ->assertSee('Srusti');
});

it('signs a user out', function () {
    $this->actingAs(User::factory()->create())
        ->post('/studio/logout')
        ->assertRedirect(route('portfolio'));

    $this->assertGuest();
});

it('has no public registration route', function () {
    $this->post('/studio/register', [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password123',
    ])->assertNotFound();

    expect(User::where('email', 'intruder@example.com')->exists())->toBeFalse();
});

it('sends a signed-in user away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/studio/login')
        ->assertRedirect(route('admin.dashboard'));
});
