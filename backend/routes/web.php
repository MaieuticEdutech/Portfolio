<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('portfolio', [
        'totalClients' => PortfolioClient::published()->count(),
    ]);
})->name('portfolio');
