<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\HandoutController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Setting::registrationEnabled(),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('documentation', 'Documentation')->name('documentation');

    Route::resource('handouts', HandoutController::class)->except('show');
});

Route::middleware(['auth', 'verified', 'can:administrate'])->group(function () {
    Route::get('administration', [AdministrationController::class, 'edit'])->name('administration.edit');
    Route::patch('administration', [AdministrationController::class, 'update'])->name('administration.update');
});

require __DIR__.'/settings.php';
