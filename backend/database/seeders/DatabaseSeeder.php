<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The factory password is well known, so this account must never
        // exist anywhere reachable. Real accounts: `php artisan studio:user`.
        if (app()->environment('local', 'testing')) {
            User::firstOrCreate(
                ['email' => 'studio@example.test'],
                ['name' => 'Studio Demo', 'password' => 'password'],
            );
        }

        $this->call(PortfolioSeeder::class);
    }
}
