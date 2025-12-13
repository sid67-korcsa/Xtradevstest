<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Models\Event;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [EventController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
            'events' => Event::all(),
        ]
    );
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/store', [EventController::class, 'store'])->name('event.store');
    Route::post('/class', [EventController::class, 'classify'])->name('event.classify');
});

require __DIR__.'/auth.php';
