<?php

use App\Models\PortfolioClient;
use App\Models\User;

it('does not seed the demo account outside local and testing', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    expect(User::where('email', 'test@example.com')->exists())->toBeFalse()
        ->and(PortfolioClient::count())->toBeGreaterThan(0);
});
