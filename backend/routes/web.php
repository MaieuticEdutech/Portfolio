<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $clients = PortfolioClient::published()->inGridOrder()->get();

    return view('portfolio', [
        'totalClients' => $clients->count(),
        'marqueeClients' => $clients,
        // The reel shows a curated front section rather than all 45.
        'galleryClients' => $clients->take(8),
        // The showreel is simply the first film with footage uploaded. Until one
        // exists the hero falls back to a generated panel rather than a gap.
        'heroFilm' => PortfolioVideo::query()
            ->whereNotNull('video_path')
            ->whereHas('client', fn ($q) => $q->published())
            ->orderBy('portfolio_client_id')
            ->orderBy('sort_order')
            ->first(),
    ]);
})->name('portfolio');

/*
|--------------------------------------------------------------------------
| Studio (admin)
|--------------------------------------------------------------------------
| Accounts are created by an administrator (`php artisan studio:user`).
| There is deliberately no public registration route.
*/

Route::prefix('studio')->name('admin.')->group(function () {
    Route::view('/login', 'admin.login')
        ->middleware('guest')
        ->name('login');

    Route::middleware('auth')->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::view('/clients', 'admin.clients')->name('clients');

        Route::get('/clients/{client}/films', function (PortfolioClient $client) {
            return view('admin.client-films', ['client' => $client]);
        })->name('clients.films');

        Route::post('/logout', function (Request $request) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portfolio');
        })->name('logout');
    });
});
