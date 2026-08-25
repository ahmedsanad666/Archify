<?php

use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('about', [AboutPageController::class, 'edit'])->name('about.edit');
        Route::put('about', [AboutPageController::class, 'update'])->name('about.update');

        Route::post('sliders/reorder', [SliderController::class, 'reorder'])
            ->name('sliders.reorder');
        Route::resource('sliders', SliderController::class)
            ->except(['show', 'create', 'edit']);
        Route::resource('services', ServiceController::class)->except(['show']);
    });
