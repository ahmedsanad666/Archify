<?php

use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\ConceptController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'locale'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('about', [AboutPageController::class, 'edit'])->name('about.edit');
        Route::put('about', [AboutPageController::class, 'update'])->name('about.update');

        Route::post('sliders/reorder', [SliderController::class, 'reorder'])
            ->name('sliders.reorder');
        Route::resource('sliders', SliderController::class)
            ->except(['show', 'create', 'edit']);
        Route::post('services/reorder', [ServiceController::class, 'reorder'])
            ->name('services.reorder');
        Route::resource('services', ServiceController::class)
            ->except(['show', 'create', 'edit']);

        Route::post('project-categories/reorder', [ProjectCategoryController::class, 'reorder'])
            ->name('project-categories.reorder');
        Route::resource('project-categories', ProjectCategoryController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::resource('concepts', ConceptController::class)
            ->except(['show', 'create', 'edit']);

        Route::post('team-members/reorder', [TeamMemberController::class, 'reorder'])
            ->name('team-members.reorder');
        Route::resource('team-members', TeamMemberController::class)
            ->except(['show', 'create', 'edit']);
    });
