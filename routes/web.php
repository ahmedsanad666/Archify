<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
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
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('contact.store');
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
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::get('/projects/{slug}', [ProjectController::class, 'show']);
        Route::get('/blogs', [BlogController::class, 'index']);
        Route::get('/blogs/{slug}', [BlogController::class, 'show']);
        Route::get('/contact', [ContactController::class, 'index']);
        Route::post('/contact', [ContactController::class, 'store'])
            ->middleware('throttle:5,1');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
