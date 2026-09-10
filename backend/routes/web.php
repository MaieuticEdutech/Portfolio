<?php

use App\Models\PortfolioClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $clients = PortfolioClient::published()->inGridOrder()->get();

    return view('portfolio', [
        'totalClients' => $clients->count(),
        'marqueeClients' => $clients,
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
