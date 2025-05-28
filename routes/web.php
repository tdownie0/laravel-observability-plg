<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// New routes for testing logging
Route::get('/throw-error', function () {
    try {
        throw new \Exception('This is a test error from the application!');
    } catch (\Exception $e) {
        Log::error('An error occurred:', [
            'exception_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    return 'Error logged. Check Loki/Grafana!';
});

Route::get('/log-info', function () {
    Log::info('This is an informational message.');

    return 'Info message logged. Check Loki/Grafana!';
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
