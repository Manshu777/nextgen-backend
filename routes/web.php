<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;

Route::get('/', function () {
    return view('welcome');
});




// Route::middleware(['ensure.token'])->post('/search-flightss', [FlightController::class, 'searchFlights']);

// Route::middleware(['ensure.token'])->post('/search-flights', [FlightController::class, 'searchFlights']);
// Route::get('/search-flights-one', [FlightController::class, 'searchFlights']);
