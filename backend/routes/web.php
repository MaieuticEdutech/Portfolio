<?php

use App\Models\PortfolioClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('portfolio', [
        'totalClients' => PortfolioClient::published()->count(),
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

        Route::post('/logout', function (Request $request) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portfolio');
        })->name('logout');
    });
});
