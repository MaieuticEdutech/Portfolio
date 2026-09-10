<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates a studio account', function () {
    $this->artisan('studio:user', ['--name' => 'Srusti', '--email' => 'srusti@example.com'])
        ->expectsQuestion('Password', 'super-secret-pw')
        ->assertSuccessful();

    $user = User::where('email', 'srusti@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Srusti')
        ->and(Hash::check('super-secret-pw', $user->password))->toBeTrue();
});

it('refuses a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('studio:user', ['--name' => 'Someone', '--email' => 'taken@example.com'])
        ->expectsQuestion('Password', 'super-secret-pw')
        ->assertFailed();

    expect(User::where('email', 'taken@example.com')->count())->toBe(1);
});

it('refuses a short password', function () {
    $this->artisan('studio:user', ['--name' => 'Someone', '--email' => 'new@example.com'])
        ->expectsQuestion('Password', 'short')
        ->assertFailed();

    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();
});
