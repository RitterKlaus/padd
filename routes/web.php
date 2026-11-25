<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HandoutActivationController;
use App\Http\Controllers\HandoutController;
use App\Http\Controllers\HandoutImageController;
use App\Http\Controllers\PaddController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Setting::registrationEnabled(),
]))->name('home');

Route::middleware('throttle:padd')->group(function () {
    Route::get('padd/{user:padd_token}', [PaddController::class, 'show'])->name('padd.show');
    Route::get('padd/{user:padd_token}/image', [PaddController::class, 'image'])->name('padd.image');
    Route::get('padd/{user:padd_token}/version', [PaddController::class, 'version'])->name('padd.version');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::inertia('documentation', 'Documentation')->name('documentation');

    Route::resource('handouts', HandoutController::class)->except('show');
    Route::get('handouts/{handout}/image', HandoutImageController::class)->name('handouts.image');
    Route::put('handouts/{handout}/activation', [HandoutActivationController::class, 'update'])->name('handouts.activation.update');
    Route::delete('handouts/{handout}/activation', [HandoutActivationController::class, 'destroy'])->name('handouts.activation.destroy');
});

Route::middleware(['auth', 'verified', 'can:administrate'])->group(function () {
    Route::get('administration', [AdministrationController::class, 'edit'])->name('administration.edit');
    Route::patch('administration', [AdministrationController::class, 'update'])->name('administration.update');
});

require __DIR__.'/settings.php';
