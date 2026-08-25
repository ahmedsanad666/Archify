<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public locale-aware routes
|--------------------------------------------------------------------------
| Named routes are unprefixed (default language). Prefixed /tr|/ar duplicates
| are unnamed; useLocale() prepends the locale segment when needed.
*/

$publicRoutes = function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [AboutController::class, 'index'])->name('about');
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::get('/faq', [FaqController::class, 'index'])->name('faqs.index');
};

Route::middleware('locale')->group($publicRoutes);

Route::prefix('{locale}')
    ->where(['locale' => 'tr|ar'])
    ->middleware('locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('/about', [AboutController::class, 'index']);
        Route::get('/team', [TeamController::class, 'index']);
        Route::get('/faq', [FaqController::class, 'index']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
