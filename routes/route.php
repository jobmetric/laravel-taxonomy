<?php

use Illuminate\Support\Facades\Route;
use JobMetric\Taxonomy\Http\Controllers\TaxonomyController;
use JobMetric\Panelio\Facades\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Taxonomy Routes
|--------------------------------------------------------------------------
|
| All Route in Laravel Taxonomy package
|
*/

// taxonomy
Route::prefix('p/{panel}/{section}/taxonomy')->name('taxonomy.')->namespace('JobMetric\Taxonomy\Http\Controllers')->group(function () {
    Route::middleware(Middleware::getMiddlewares())->group(function () {
        Route::post('{type}/set-translation', [TaxonomyController::class, 'setTranslation'])->name('set-translation');
        Route::get('{type}/import', [TaxonomyController::class, 'import'])->name('import');
        Route::get('{type}/export', [TaxonomyController::class, 'export'])->name('export');
        Route::options('{type}', [TaxonomyController::class, 'options'])->name('options');
        Route::resource('{type}', TaxonomyController::class)->except(['show', 'destroy'])->parameter('{type}', 'jm_taxonomy:id');
    });
});
