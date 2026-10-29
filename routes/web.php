<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderStartController;
use Illuminate\Support\Facades\Route;

// Public site: Indonesian at the root, English under /en (see SetLocale).
Route::get('/', HomeController::class)->name('home');
Route::get('pesan', OrderStartController::class)->name('order.start');

Route::prefix('en')->name('en.')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('pesan', OrderStartController::class)->name('order.start');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::inertia('playground', 'playground')->name('playground');
