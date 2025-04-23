<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-email-view', function () {
    $ticket = [
        'pnr' => 'TEST123',
        'user_name' => 'Test User',
        'flight_name' => 'Test Airlines',
        'flight_number' => 'TA001',
        'departure_from' => 'Test City',
        'flight_date' => '2025-04-25 10:00:00',
        'arrival_to' => 'Test Destination',
        'total_fare' => 1000,
    ];
    return view('emails.ticket', compact('ticket'));
});

use Illuminate\Support\Facades\Artisan;


Route::get('/create-storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});

Route::get("/destory-storage-link",function(){
    if (File::exists(public_path('storage'))) {
        File::delete(public_path('storage'));
        return 'Unlink successful!';
    } else {
        return 'Symlink does not exist!';
    }
}); 








// Route::middleware(['ensure.token'])->post('/search-flightss', [FlightController::class, 'searchFlights']);
// Route::middleware(['ensure.token'])->post('/search-flights', [FlightController::class, 'searchFlights']);
// Route::get('/search-flights-one', [FlightController::class, 'searchFlights']);
