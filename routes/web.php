<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderFlowController;
use Illuminate\Support\Facades\Route;

// Public site: Indonesian at the root, English under /en (see SetLocale).
$publicSite = function () {
    Route::get('/', HomeController::class)->name('home');

    Route::prefix('pesan')->name('order.')->controller(OrderFlowController::class)->group(function () {
        Route::get('/', 'date')->name('date');
        Route::post('/', 'storeDate')->name('date.store');
        Route::get('kue', 'cake')->name('cake');
        Route::post('kue', 'storeCake')->name('cake.store');
        Route::get('detail', 'details')->name('details');
        Route::post('detail', 'storeDetails')->name('details.store');
        Route::get('kartu', 'card')->name('card');
        Route::post('kartu', 'storeCard')->name('card.store');
        Route::get('bayar', 'checkout')->name('checkout');
        Route::post('bayar', 'placeOrder')->name('checkout.store');
        Route::get('selesai/{code}', 'done')->name('done');
        Route::get('selesai/{code}/kalender.ics', 'calendar')->name('calendar');
    });
};

Route::group([], $publicSite);
Route::prefix('en')->name('en.')->group($publicSite);

Route::inertia('playground', 'playground')->name('playground');
