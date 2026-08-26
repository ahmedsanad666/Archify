<?php

use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ConceptController;
use App\Http\Controllers\Admin\CoreValueController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'locale'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

        Route::resource('leads', LeadController::class)->only(['index']);
        Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])
            ->name('leads.update-status');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('about', [AboutPageController::class, 'edit'])->name('about.edit');
        Route::put('about', [AboutPageController::class, 'update'])->name('about.update');

        Route::post('statistics/reorder', [StatisticController::class, 'reorder'])
            ->name('statistics.reorder');
        Route::resource('statistics', StatisticController::class)
            ->except(['index', 'show', 'create', 'edit']);

        Route::post('core-values/reorder', [CoreValueController::class, 'reorder'])
            ->name('core-values.reorder');
        Route::resource('core-values', CoreValueController::class)
            ->except(['index', 'show', 'create', 'edit']);

        Route::post('sliders/reorder', [SliderController::class, 'reorder'])
            ->name('sliders.reorder');
        Route::resource('sliders', SliderController::class)
            ->except(['show', 'create', 'edit']);
        Route::post('services/reorder', [ServiceController::class, 'reorder'])
            ->name('services.reorder');
        Route::resource('services', ServiceController::class)
            ->except(['show', 'create', 'edit']);

        Route::post('blog-categories/reorder', [BlogCategoryController::class, 'reorder'])
            ->name('blog-categories.reorder');
        Route::resource('blog-categories', BlogCategoryController::class)
            ->except(['show', 'create', 'edit']);

        Route::resource('blogs', BlogController::class)->except(['show']);

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

        Route::post('testimonials/reorder', [TestimonialController::class, 'reorder'])
            ->name('testimonials.reorder');
        Route::resource('testimonials', TestimonialController::class)
            ->except(['show', 'create', 'edit']);

        Route::post('faqs/reorder', [FaqController::class, 'reorder'])
            ->name('faqs.reorder');
        Route::resource('faqs', FaqController::class)
            ->except(['show', 'create', 'edit']);

        Route::get('media', [MediaLibraryController::class, 'index'])->name('media.index');
        Route::delete('media/{medium}', [MediaLibraryController::class, 'destroy'])
            ->name('media.destroy');
    });
